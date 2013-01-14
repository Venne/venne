<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace CmsModule\Components\Table;

use Venne;
use Venne\Application\UI\Control;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class TableControl extends Control
{

	const TYPE_TEXT = 'CmsModule\Components\Table\Columns\BaseColumn';

	const SORT_ASC = 'ASC';

	const SORT_DESC = 'DESC';

	/** @persistent */
	public $sort = array();

	/** @persistent */
	public $editId;

	/** @persistent */
	public $editForm;

	/** @persistent */
	public $createForm;

	/** @persistent */
	public $floor;

	/** @persistent */
	public $key;

	/** @persistent */
	public $filters = array();

	/** @persistent */
	public $perPage;

	/** @var string */
	protected $templateFile;

	/** @var \DoctrineModule\Repositories\BaseRepository */
	protected $repository;

	/** @var string */
	protected $primaryColumn;

	/** @var Column[] */
	protected $columns = array();

	/** @var array */
	protected $actions = array();

	/** @var Form[] */
	protected $forms = array();

	/** @var TableControl[] */
	protected $floors = array();

	/** @var int */
	protected $_formCounter = 0;

	/** @var array */
	protected $buttons = array();

	/** @var array */
	protected $globalActions = array();

	/** @var \Nette\Callback */
	protected $dqlCallback;

	/** @var int */
	protected $defaultPerPage = 20;

	/** @var array */
	protected $defaultFilters = array();

	/** @var array */
	protected $defaultSort = array();

	/** @var array */
	protected $perPageList = array(10, 20, 30, 50, 100, 200, 500);

	/** @var array */
	public $onAttached;


	public function __construct($primaryColumn = 'id')
	{
		parent::__construct();

		$this->primaryColumn = $primaryColumn;
	}


	protected function attached($presenter)
	{
		parent::attached($presenter);

		$this->sort = $this->defaultSort + $this->sort;
		$this->filters = $this->defaultFilters + $this->filters;

		$this->onAttached();
	}


	public function getPrimaryColumn()
	{
		return $this->primaryColumn;
	}


	public function setTemplateFile($file)
	{
		$this->templateFile = $file;
	}


	public function getRepository()
	{
		return $this->repository;
	}


	public function setRepository(\DoctrineModule\Repositories\BaseRepository $repository)
	{
		$this->repository = $repository;
	}


	/**
	 * @param int $defaultPerPage
	 */
	public function setDefaultPerPage($defaultPerPage)
	{
		$this->defaultPerPage = $defaultPerPage;
	}


	/**
	 * @param \Venne\Forms\FormFactory $formFactory
	 * @param null $width
	 * @param null $height
	 * @return Form
	 */
	public function addForm(\Venne\Forms\FormFactory $formFactory, $title, $entityFactory = NULL, $type = NULL)
	{
		return $this['_form_' . $this->_formCounter] = $this->forms['_form_' . $this->_formCounter++] = new Form($formFactory, $title, $entityFactory, $type);
	}


	/**
	 * @param $name
	 * @param $floorFactory
	 * @throws \Nette\InvalidArgumentException
	 */
	public function addFloor($name, $floorFactory)
	{
		if (isset($this->floors[$name])) {
			throw new \Nette\InvalidArgumentException("Floor '{$name}' already exists");
		}

		$this['_floor_' . $name] = $floorFactory();
		$floor = explode('-', $this->floor, 2);
		if (isset($floor[1])) {
			$this['_floor_' . $name]->floor = $floor[1];
		}
	}


	/**
	 * @param $name
	 * @param $title
	 * @param null $width
	 * @param null $callback
	 * @return IColumn
	 */
	public function addColumn($name, $title, $type = self::TYPE_TEXT)
	{
		$type = '\\' . trim($type, '\\');
		return $this->columns[$name] = new $type($this, $name, $title);
	}


	public function addColumnFloor($floor, $name, $title, $width = NULL, $callback = NULL)
	{
		if (!isset($this['_floor_' . $floor])) {
			throw new \Nette\InvalidArgumentException("Floor '{$floor}' does not exist");
		}

		$_this = $this;

		$this->columns[$name] = array(
			'title' => $title,
			'width' => $width,
			'callback' => function ($entity) use ($_this, $floor) {
				$html = \Nette\Utils\Html::el('a');
				$html->class = 'ajax';
				$html->attrs['href'] = $_this->link('this', array('floor' => $floor, "_floor_{$floor}-key" => $entity->{$this->primaryColumn}));
				$html->setText($entity->text);
				return $html;
			},
		);
	}


	/**
	 * @param $name
	 * @param $title
	 * @return Button
	 */
	public function addAction($name, $title)
	{
		return $this->actions[$name] = $this[$name] = new Button($title);
	}


	/**
	 * @param $name
	 * @param $title
	 * @return Button
	 */
	public function addActionDelete($name, $title)
	{
		$_this = $this;

		$control = $this->addAction($name, $title);
		$control->onClick[] = function (Button $button, $entity) use ($_this) {
			$_this->getRepository()->delete($entity);
		};
		$control->onSuccess[] = function (TableControl $table) use ($_this) {
			$presenter = $table->getPresenter();
			$presenter->flashMessage('Items has been removed', 'success');

			// list back
			if ($_this->countItems() === 0 && $_this['vp']->page > 1) {
				$_this['vp']->page--;
				$args = array('vp' => array('page' => $_this['vp']->page));
			} else {
				$args = array();
			}

			if (!$presenter->isAjax()) {
				$presenter->redirect('this', $args);
			}
			$_this->invalidateControl('table');
			$presenter->payload->url = $presenter->link('this', $args);
		};
		return $control;
	}


	/**
	 * @param $name
	 * @param $title
	 * @return Button
	 */
	public function addActionEdit($name, $title, Form $form)
	{
		$_this = $this;

		$control = $this->addAction($name, $title);
		$control->onClick[] = function (Button $button, $entity) use ($_this, $form) {
			$table = $button->getTable();
			$presenter = $table->getPresenter();

			if (!$presenter->isAjax()) {
				$table->redirect('edit!', array('editForm' => $form->getName(), 'editId' => $entity->{$_this->primaryColumn}));
			}
			$presenter->payload->url = $table->link('edit!', array('editForm' => $form->getName(), 'editId' => $entity->{$_this->primaryColumn}));
			$table->editForm = $form->getName();
			$table->editId = $entity->{$_this->primaryColumn};
			$table->handleEdit();
		};
		return $control;
	}


	/**
	 * @param Button $button
	 * @return TableControl
	 */
	public function setGlobalAction(Button $button)
	{
		$this->globalActions[$button->getName()] = $button;

		return $this;
	}


	public function addButton($name, $title, $icon = NULL)
	{
		return $this->getNavbar()->addSection($name, $title, $icon);
	}


	public function addButtonCreate($name, $title, Form $form, $icon = NULL)
	{
		$_this = $this;

		$control = $this->addButton($name, $title, $icon);
		$control->onClick[] = function (\CmsModule\Components\Navbar\Section $button) use ($_this, $form) {
			if (!$_this->presenter->isAjax()) {
				$_this->redirect('create!', array('createForm' => $form->getName()));
			}
			$_this->presenter->payload->url = $_this->link('create!', array('createForm' => $form->getName()));
			$_this->createForm = $form->getName();
			$_this->handleCreate();
		};
		return $control;
	}


	public function handleSort($column)
	{
		if (!isset($this->sort[$column])) {
			$this->sort[$column] = self::SORT_ASC;
		} else if ($this->sort[$column] === self::SORT_ASC) {
			$this->sort[$column] = self::SORT_DESC;
		} else {
			unset($this->sort[$column]);
		}

		if (!$this->presenter->isAjax()) {
			$this->redirect('this');
		}

		$this->invalidateControl('table');
		$this->presenter->payload->url = $this->link('this');
	}


	public function handleDoAction($name, $id)
	{
		$button = $this[$name];
		$button->onClick($button, $this->repository->find($id));
		$button->onSuccess($this);
	}


	public function handleEdit()
	{
		if (!$this->presenter->isAjax()) {
			$this->redirect('this');
		}

		$this->invalidateControl('form');
		$this->presenter->payload->url = $this->link('this');
	}


	public function handleCreate()
	{
		$this->invalidateControl('form');

		if (!$this->presenter->isAjax()) {
			$this->redirect('this');
		}

		$this->presenter->payload->url = $this->link('this');
	}


	public function render()
	{
		if ($this->templateFile) {
			$this->template->setFile($this->templateFile);
		}

		if ($this->floor) {
			$floor = explode('-', $this->floor, 2);
			$this['_floor_' . $floor[0]]->render();
		} else {
			$this->template->columns = $this->columns;
			$this->template->actions = $this->actions;
			$this->template->globalActions = $this->globalActions;
			$this->template->primaryColumn = $this->primaryColumn;
			$this->template->sort = $this->sort;

			$this->template->render();
		}
	}


	public function countItems()
	{
		return count($this->getItems());
	}


	public function getItems()
	{
		return $this->getQueryBuilder()->getQuery()->getResult();
	}


	public function getQueryBuilder($select = 'a')
	{
		$dql = $this->getRawQueryBuilder($select);

		if (count($this->sort) > 0) {
			foreach ($this->sort as $column => $order) {
				$dql = $dql->orderBy('a.' . $column, $order);
			}
		}

		if ($this->perPage ? $this->perPage : $this->defaultPerPage) {
			$dql = $dql
				->setMaxResults($this->perPage ? $this->perPage : $this->defaultPerPage)
				->setFirstResult(($this["vp"]->page - 1) * $dql->getMaxResults());
		}

		return $dql;
	}


	public function getRawQueryBuilder($select = 'a')
	{
		$dql = $this->repository->createQueryBuilder($select);

		if ($this->dqlCallback) {
			$fn = $this->dqlCallback;
			$fn($dql);
		}

		if (count($this->filters) > 0) {
			foreach ($this->filters as $key => $value) {
				$dql = $this->columns[$key]->getFilter()->setDql($dql, $value);
			}
		}

		return $dql;
	}


	protected function createComponentVp()
	{
		$vp = new \CmsModule\Components\VisualPaginator;
		$pg = $vp->getPaginator();
		$pg->setItemsPerPage($this->perPage ? $this->perPage : $this->defaultPerPage);
		$pg->setItemCount($this->getRawQueryBuilder()->select("COUNT(a.{$this->primaryColumn})")->getQuery()->getSingleScalarResult());
		return $vp;
	}


	protected function createComponentActionForm()
	{
		$form = $this->presenter->context->createCms__admin__basicForm();
		//$form->getElementPrototype()->class[] = 'ajax';

		// filters
		$filters = $form->addContainer('filters');
		foreach ($this->columns as $column) {
			if ($filter = $column->getFilter()) {
				$filter->getControl($filters);
			}
		}
		$filters->setDefaults($this->filters);

		// items
		$items = $form->addContainer('items');
		foreach ($this->getItems() as $entity) {
			$items->addCheckbox('item_' . $entity->{$this->primaryColumn});
		}

		// actions
		$items = array();
		foreach ($this->globalActions as $key => $item) {
			$items[$key] = $item->getLabel();
		}

		// perPage
		$form->addSelect('perPage')->setItems($this->perPageList, FALSE)->setDefaultValue($this->getParameter('perPage', $this->defaultPerPage));

		$form->addSelect('action', 'Action', $items);
		$form->addSubmit('_submit', 'Apply');
		$form->addSubmit('filters2', 'Apply');
		$form->addSubmit('reset2', 'Reset');
		$form->addSubmit('perPageSubmit', 'Apply');
		$form->onSuccess[] = $this->formSuccess;
		return $form;
	}


	public function formSuccess(Venne\Forms\Form $form)
	{
		if ($form['_submit']->isSubmittedBy()) {
			$action = $form['action']->getValue();
			$button = $this[$action];

			$values = $form['items']->getValues();
			foreach ($values as $key => $value) {
				if ($value) {
					$button->onClick($button, $this->repository->find(substr($key, 5)));
				}
			}

			$button->onSuccess($this);
		} else if ($form['filters2']->isSubmittedBy()) {
			$this->filters = $form['filters']->getValues();

			foreach ($this->filters as $key => $value) {
				if (!$value) {
					unset($this->filters[$key]);
				}
			}

			$this['vp']->page = NULL;
			if (!$this->presenter->isAjax()) {
				$this->redirect('this');
			}

			$this->presenter->payload->url = $this->link('this');
		} else if ($form['reset2']->isSubmittedBy()) {
			$form['filters']->setValues(array());
			$this->filters = array();

			$this['vp']->page = NULL;
			if (!$this->presenter->isAjax()) {
				$this->redirect('this');
			}

			$this->presenter->payload->url = $this->link('this');
		} else if ($form['perPageSubmit']->isSubmittedBy()) {
			$this->perPage = $form['perPage']->value;
			$this['vp']->page = NULL;

			if (!$this->presenter->isAjax()) {
				$this->redirect('this');
			}

			$this->presenter->payload->url = $this->link('this');
		}
	}


	protected function createComponentEditForm()
	{
		$formFactory = $this->forms[$this->editForm];
		$entity = $this->getRepository()->findOneBy(array($this->primaryColumn => $this->editId));

		/** @var $form \Venne\Forms\Form */
		$form = $formFactory->getFactory()->invoke($entity);
		$formFactory->onCreate($form);
		$form->onError[] = $this->formError;
		$form->onSuccess[] = $this->formEditSuccess;

		if ($form->hasSaveButton()) {
			$form->getSaveButton()->getControlPrototype()->onClick = '_a = window.alert; window.alert = function() {}; if(Nette.validateForm(this)) { $(this).parents(".modal").each(function(){ $(this).modal("hide"); }); } window.alert = _a';
		}

		return $form;
	}


	public function formEditSuccess(\Venne\Forms\Form $form)
	{
		if ($form->hasSaveButton() && $form->isSubmitted() === $form->getSaveButton()) {
			if (!$this->presenter->isAjax()) {
				$this->redirect('edit!', array('editForm' => NULL, 'editId' => NULL));
			}
			$this->getPresenter()->invalidateControl('content');
			$this->presenter->payload->url = $this->link('edit!', array('editForm' => NULL, 'editId' => NULL));
			$this->editForm = NULL;
			$this->editId = NULL;
			$this->handleEdit();
		} else {
			$this->invalidateControl('editForm');
		}
	}


	protected function createComponentCreateForm()
	{
		$formFactory = $this->forms[$this->createForm];
		$entityFactory = $formFactory->getEntityFactory();
		$entity = $entityFactory ? $entityFactory() : $this->getRepository()->createNew();

		/** @var $form \Venne\Forms\Form */
		$form = $formFactory->getFactory()->invoke($entity);
		$formFactory->onCreate($form);
		$form->onError[] = $this->formError;
		$form->onSuccess[] = $this->formCreateSuccess;

		if ($form->hasSaveButton()) {
			$form->getSaveButton()->getControlPrototype()->onClick = '_a = window.alert; window.alert = function() {}; if(Nette.validateForm(this)) { $(this).parents(".modal").each(function(){ $(this).modal("hide"); }); } window.alert = _a';
		}

		return $form;
	}


	public function formCreateSuccess(\Venne\Forms\Form $form)
	{
		if ($form->hasSaveButton() && $form->isSubmitted() === $form->getSaveButton()) {
			if (!$this->presenter->isAjax()) {
				$this->redirect('create!', array('createForm' => NULL));
			}
			$this->getPresenter()->invalidateControl('content');
			$this->presenter->payload->url = $this->link('create!', array('createForm' => NULL));
			$this->createForm = NULL;
			$this->handleCreate();
		} else {
			$this->invalidateControl('createForm');
		}
	}


	public function formError(\Venne\Forms\Form $form)
	{
		if ($form->hasSaveButton() && $form->isSubmitted() === $form->getSaveButton()) {
			$this->invalidateControl('form');
		} else {
			if ($this->editForm) {
				$this->invalidateControl('editForm');
			}
			if ($this->createForm) {
				$this->invalidateControl('createForm');
			}
		}
	}


	/**
	 * @param \Nette\Callback $dql
	 */
	public function setDql($dql)
	{
		$this->dqlCallback = $dql;
	}


	/**
	 * @return \Nette\Callback
	 */
	public function getDql()
	{
		return $this->dqlCallback;
	}


	/**
	 * @param array $perPageList
	 */
	public function setPerPageList($perPageList)
	{
		$this->perPageList = $perPageList;
	}


	/**
	 * @return array
	 */
	public function getPerPageList()
	{
		return $this->perPageList;
	}


	/**
	 * @param array $defaultFilters
	 */
	public function setDefaultFilters($defaultFilters)
	{
		$this->defaultFilters = $defaultFilters;
	}


	/**
	 * @return array
	 */
	public function getDefaultFilters()
	{
		return $this->defaultFilters;
	}


	/**
	 * @param array $defaultSort
	 */
	public function setDefaultSort($defaultSort)
	{
		$this->defaultSort = $defaultSort;
	}


	/**
	 * @return array
	 */
	public function getDefaultSort()
	{
		return $this->defaultSort;
	}


	/***************************** Navbar **************************** */


	/**
	 * @return \CmsModule\Components\Navbar\NavbarControl
	 */
	public function getNavbar()
	{
		$this->template->showNavbar = true;

		return $this['navbar'];
	}


	/**
	 * @return \CmsModule\Components\Navbar\NavbarControl
	 */
	protected function createComponentNavbar()
	{
		return new \CmsModule\Components\Navbar\NavbarControl();
	}
}
