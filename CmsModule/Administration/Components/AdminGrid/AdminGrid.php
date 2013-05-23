<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace CmsModule\Administration\Components\AdminGrid;

use CmsModule\Components\Grido\Actions\CallbackAction;
use CmsModule\Components\Grido\Grid;
use CmsModule\Components\Navbar\NavbarControl;
use CmsModule\Components\Navbar\Section;
use DoctrineModule\Repositories\BaseRepository;
use Grido\DataSources\Doctrine;
use Nette\Callback;
use Venne\Application\UI\Control;
use Venne\Forms\FormFactory;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class AdminGrid extends Control
{
	/**
	 * @var string
	 * @persistent
	 */
	public $id;

	/**
	 * @var string
	 * @persistent
	 */
	public $formName;

	/**
	 * @var string
	 * @persistent
	 */
	public $floor;

	/**
	 * @var string
	 * @persistent
	 */
	public $floorId;

	/** @var array */
	public $onAttached;

	/** @var array */
	public $onRender;

	/** @var AdminGrid[] */
	protected $floors = array();

	/** @var AdminGrid */
	protected $parentFloor;

	/** @var BaseRepository */
	protected $repository;

	/** @var Callback */
	protected $tableFactory;

	/** @var Callback */
	protected $navbarFactory;

	/** @var NavbarControl */
	protected $navbar;

	/** @var Form[] */
	protected $navbarForms = array();

	/** @var Form[] */
	protected $actionForms = array();


	public function __construct(BaseRepository $repository)
	{
		parent::__construct();

		$this->repository = $repository;
	}


	protected function attached($presenter)
	{
		parent::attached($presenter);

		$this->onAttached($this);
	}


	/**
	 * @param \DoctrineModule\Repositories\BaseRepository $repository
	 */
	public function setRepository($repository)
	{
		$this->repository = $repository;
	}


	/**
	 * @return \DoctrineModule\Repositories\BaseRepository
	 */
	public function getRepository()
	{
		return $this->repository;
	}


	public function handleClose()
	{
		if (!$this->presenter->isAjax()) {
			$this->redirect('this');
		}

		$this->id = NULL;
		$this->formName = NULL;
		$this->invalidateControl('navbarFormContainer');
		$this->invalidateControl('actionFormContainer');
		$this->presenter->payload->url = $this->link('this');
	}


	public function handleFloor()
	{
		if (!$this->presenter->isAjax()) {
			$this->redirect('this');
		}

		$this->invalidateControl('table');
		$this->invalidateControl('navbar');
		$this->invalidateControl('breadcrumb');
		$this->presenter->payload->url = $this->link('this');
	}


	/**
	 * @param FormFactory $formFactory
	 * @param Section $section
	 */
	public function connectFormWithNavbar(Form $form, Section $section)
	{
		$this->navbarForms[$section->getName()] = $form;

		$_this = $this;
		$section->onClick[] = function ($section) use ($_this) {
			$_this->id = NULL;
			$_this->invalidateControl('navbarFormContainer');
			$_this->setFormName($section->getName());
		};
		return $this;
	}


	public function connectFormWithAction(Form $form, CallbackAction $action)
	{
		$this->actionForms[$action->getName()] = $form;

		$_this = $this;
		$action->onClick[] = function ($action, $id) use ($_this) {
			$_this->id = $id;
			$_this->invalidateControl('actionFormContainer');
			$_this->setFormName($action->getName());
		};
		return $this;
	}


	public function connectActionAsDelete(CallbackAction $action)
	{
		$action->onClick[] = $this->tableDelete;
		$action->setConfirm(function ($entity) {
			if (method_exists($entity, '__toString')) {
				return "Really delete '{$entity}'?";
			}
			return 'Really delete?';
		});

		$this->getTable()->setOperations(array('delete' => 'Delete'), $this->tableDelete);
		return $this;
	}


	public function connectActionWithFloor(CallbackAction $action, AdminGrid $adminGrid, $name)
	{
		$this->floors[$name] = $adminGrid;
		$adminGrid->setParentFloor($this);

		$_this = $this;
		$action->onClick[] = function ($action, $id) use ($_this, $name) {
			$_this->floorId = $id;
			$_this->floor = $name;

			$_this->handleFloor();
		};

		return $this;
	}


	/**
	 * @param $parentFloor
	 * @return $this
	 */
	public function setParentFloor($parentFloor)
	{
		$this->parentFloor = $parentFloor;
		return $this;
	}


	/**
	 * @return \CmsModule\Administration\Components\AdminGrid\AdminGrid
	 */
	public function getParentFloor()
	{
		return $this->parentFloor;
	}


	/**
	 * @return array|AdminGrid[]
	 */
	public function getFloors()
	{
		return $this->floors;
	}


	public function createForm(\Venne\Forms\FormFactory $formFactory, $title, $entityFactory = NULL, $type = NULL)
	{
		return new Form($formFactory, $title, $entityFactory, $type);
	}


	/**
	 * @param $tableFactory
	 * @return $this
	 */
	public function setTableFactory($tableFactory)
	{
		$this->tableFactory = $tableFactory;
		return $this;
	}


	/**
	 * @return callable
	 */
	public function getTableFactory()
	{
		return $this->tableFactory;
	}


	/**
	 * @return Grid
	 */
	public function getTable()
	{
		return $this['table'];
	}


	/**
	 * @return NavbarControl
	 */
	public function getNavbar()
	{
		if (!$this->navbar) {
			$this->navbar = $this['navbar'];
		}

		return $this->navbar;
	}


	public function setNavbar(NavbarControl $navbar = NULL)
	{
		$this->navbar = $navbar;

		if (!$this->navbar) {
			unset($this['navbar']);
		}
	}


	/**
	 * @param string $formName
	 */
	public function setFormName($formName)
	{
		$this->formName = $formName;
	}


	/**
	 * @return string
	 */
	public function getFormName()
	{
		return $this->formName;
	}


	/**
	 * @return Grid
	 */
	protected function createComponentTable()
	{
		if ($this->tableFactory) {
			$grid = Callback::create($this->tableFactory)->invoke();
		} else {
			$grid = new Grid;
			$grid->addAction('_groupstart', 'Group start')->setCustomRender(function () {
				return '<div class="btn-group">';
			});
			$grid->setModel(new Doctrine($this->repository->createQueryBuilder('a')));
		}

		return $grid;
	}


	/**
	 * @return NavbarControl
	 */
	protected function createComponentNavbar()
	{
		$navbar = $this->navbarFactory ? Callback::create($this->navbarFactory)->invoke() : new NavbarControl;
		return $navbar;
	}


	protected function createComponentNavbarForm()
	{
		$form = $this->navbarForms[$this->formName];
		$entity = $form->getEntityFactory() ? Callback::create($form->getEntityFactory())->invoke() : $this->repository->createNew();

		$form = $form->getFactory()->invoke($entity);
		$form->onSuccess[] = $this->navbarFormSuccess;
		$form->onError[] = $this->navbarFormError;

		return $form;
	}


	protected function createComponentActionForm()
	{
		$form = $this->actionForms[$this->formName];
		$entity = $this->repository->find($this->id);

		$form = $form->getFactory()->invoke($entity);
		$form->onSuccess[] = $this->actionFormSuccess;
		$form->onError[] = $this->actionFormError;

		return $form;
	}


	public function navbarFormSuccess(\Venne\Forms\Form $form)
	{
		$this->invalidateControl('navbarForm');

		if ($form->hasSaveButton() && $form->isSubmitted() === $form->getSaveButton()) {

			$this->formName = NULL;
			if (!$this->presenter->isAjax()) {
				$this->redirect('this', array('formName' => NULL));
			}

			$this->invalidateControl('table');
			$this->presenter->payload->url = $this->link('this', array('formName' => NULL));
		}
	}


	public function actionFormSuccess(\Venne\Forms\Form $form)
	{
		$this->invalidateControl('actionForm');

		if ($form->hasSaveButton() && $form->isSubmitted() === $form->getSaveButton()) {

			$this->id = NULL;
			$this->formName = NULL;
			if (!$this->presenter->isAjax()) {
				$this->redirect('this', array('formName' => NULL, 'id' => NULL));
			}

			$this->invalidateControl('table');
			$this->presenter->payload->url = $this->link('this', array('formName' => NULL, 'id' => NULL));
		}
	}


	public function navbarFormError(\Venne\Forms\Form $form)
	{
		$this->invalidateControl('navbarForm');
	}


	public function actionFormError(\Venne\Forms\Form $form)
	{
		$this->invalidateControl('actionForm');
	}


	public function render()
	{
		$this->onRender($this);

		if (!$this->tableFactory) {
			$grid = $this['table'];
			$grid->addAction('_groupend', 'Group end')->setCustomRender(function () {
				return '</div>';
			});
		}

		if ($this->formName) {
			$this->template->form = $this->id ? $this->actionForms[$this->formName] : $this->navbarForms[$this->formName];
		}
		$this->template->showNavbar = $this->navbar;
		$this->template->render();
	}


	/** ------------------------ Callbacks --------------------------------- */

	public function tableDelete($action, $id, $redirect = TRUE)
	{
		if (is_array($id)) {
			foreach ($id as $item) {
				$this->tableDelete($action, $item, FALSE);
			}
		} else {
			$this->repository->delete($this->repository->find($id));
		}

		if ($redirect) {
			if (!$this->presenter->isAjax()) {
				$this->redirect('this');
			}

			$this->invalidateControl('table');
			$this->presenter->payload->url = $this->link('this');
		}
	}
}
