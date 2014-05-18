<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\System\Components\AdminGrid;

use Grido\Components\Actions\Event;
use Grido\DataSources\Doctrine;
use Grido\Grid;
use Kdyby\Doctrine\Entities\BaseEntity;
use Kdyby\Doctrine\EntityDao;
use Nette\Callback;
use Venne\Bridges\Kdyby\DoctrineForms\FormFactoryFactory;
use Venne\Forms\IFormFactory;
use Venne\System\Components\IGridoFactory;
use Venne\System\Components\INavbarControlFactory;
use Venne\System\Components\NavbarControl;
use Venne\System\Components\Section;
use Venne\System\UI\Control;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class AdminGrid extends Control
{

	const MODE_MODAL = 'modal';

	const MODE_PLACE = 'place';

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
	public $mode = self::MODE_MODAL;

	/** @var array */
	public $onAttached;

	/** @var array */
	public $onRender;

	/** @var EntityDao */
	protected $dao;

	/** @var INavbarControlFactory */
	protected $navbarFactory;

	/** @var NavbarControl */
	protected $navbar;

	/** @var Form[] */
	protected $navbarForms = array();

	/** @var Form[] */
	protected $actionForms = array();

	/** @var FormFactoryFactory */
	private $formFactoryFactory;

	/** @var IGridoFactory */
	private $gridoFactory;


	public function __construct(
		EntityDao $dao = NULL,
		INavbarControlFactory $navbarFactory,
		FormFactoryFactory $formFactoryFactory,
		IGridoFactory $gridoFactory
	)
	{
		parent::__construct();

		$this->dao = $dao;
		$this->navbarFactory = $navbarFactory;
		$this->formFactoryFactory = $formFactoryFactory;
		$this->gridoFactory = $gridoFactory;
	}


	protected function attached($presenter)
	{
		parent::attached($presenter);

		$this->onAttached($this);

		if ($this->presenter->getParameter('do') === NULL && $this->presenter->isAjax()) {
			$this->redrawControl('table');
			$this->redrawControl('navbar');
			$this->redrawControl('breadcrumb');
		}
	}


	/**
	 * @param EntityDao $dao
	 */
	public function setDao(EntityDao $dao)
	{
		$this->dao = $dao;
	}


	/**
	 * @return EntityDao
	 */
	public function getDao()
	{
		return $this->dao;
	}


	public function handleClose()
	{
		$this->redirect('this', array('id' => NULL, 'formName' => NULL));
	}


	public function connectFormWithNavbar(Form $form, Section $section, $mode = self::MODE_MODAL)
	{
		$this->navbarForms[$section->getName()] = $form;

		$section->onClick[] = function ($section) use ($mode) {
			$this->redirect('this', array('id' => NULL, 'mode' => $mode, 'formName' => $section->getName()));
		};
		return $this;
	}


	public function connectFormWithAction(Form $form, Event $action, $mode = self::MODE_MODAL)
	{
		$this->actionForms[$action->getName()] = $form;

		$action->onClick[] = function ($id, $action) use ($mode) {
			$this->redirect('this', array('id' => $id, 'mode' => $mode, 'formName' => $action->getName()));
		};
		return $this;
	}


	public function connectActionAsDelete(Event $action)
	{
		$action->onClick[] = $this->tableDelete;
		$action->setConfirm(function ($entity) {
			if (method_exists($entity, '__toString')) {
				return "Really delete '{$entity}'?";
			}
			return 'Really delete?';
		});

		$this->getTable()->setOperation(array('delete' => 'Delete'), $this->tableDelete);
		return $this;
	}


	public function createForm(IFormFactory $formFactory, $title, $entityFactory = NULL, $type = NULL)
	{
		return new Form($formFactory, $title, $entityFactory, $type);
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
		$grid = $this->gridoFactory->create();

		if ($this->dao) {
			$grid->setModel(new Doctrine($this->dao->createQueryBuilder('a')));
		}

		return $grid;
	}


	/**
	 * @return NavbarControl
	 */
	protected function createComponentNavbar()
	{
		$navbar = $this->navbarFactory ? $this->navbarFactory->create() : new NavbarControl;
		return $navbar;
	}


	protected function createComponentNavbarForm()
	{
		$form = $this->navbarForms[$this->formName];

		if ($form->getEntityFactory()) {
			$entity = Callback::create($form->getEntityFactory())->invoke();
		} else {
			$class = $this->dao->getClassName();
			$entity = new $class;
		}

		$form = $this->formFactoryFactory
			->create($form->getFactory())
			->setEntity($entity)
			->create();

		$form->onSubmit[] = function () {
			if ($this->presenter->isAjax()) {
				$this->redrawControl('navbarForm');
			}
		};
		$form->onSuccess[] = $this->navbarFormSuccess;
		$form->onError[] = $this->navbarFormError;

		if ($this->mode == self::MODE_PLACE) {
			$form->addSubmit('_cancel', 'Cancel')
				->setValidationScope(FALSE)
				->onClick[] = function () {
				$this->redirect('this', array('formName' => NULL, 'mode' => NULL));
			};
		}

		return $form;
	}


	protected function createComponentActionForm()
	{
		/** @var Form $form */
		$form = $this->actionForms[$this->formName];
		$form = $this->formFactoryFactory
			->create($form->getFactory())
			->setEntity($this->getCurrentEntity())
			->create();

		$form->onSubmit[] = function () {
			if ($this->presenter->isAjax()) {
				$this->redrawControl('actionForm');
			}
		};
		$form->onSuccess[] = $this->actionFormSuccess;
		$form->onError[] = $this->actionFormError;

		if ($this->mode == self::MODE_PLACE) {
			$form->addSubmit('_cancel', 'Cancel')
				->setValidationScope(FALSE)
				->onClick[] = function () {
				$this->redirect('this', array('formName' => NULL, 'mode' => NULL));
			};
		}

		return $form;
	}


	/**
	 * @return BaseEntity
	 */
	public function getCurrentEntity()
	{
		if ($this->id) {
			return $this->getDao()->find($this->id);
		}

		$class = $this->dao->getClassName();
		return new $class;
	}


	public function navbarFormSuccess(\Nette\Application\UI\Form $form)
	{
		if (isset($form['_submit']) && $form->isSubmitted() === $form['_submit']) {
			if (!$this->presenter->isAjax()) {
				$this->redirect('this', array('formName' => NULL, 'mode' => NULL));
			}
			$this->formName = NULL;
			$this->mode = NULL;
		}
	}


	public function actionFormSuccess(\Nette\Application\UI\Form $form)
	{
		if (isset($form['_submit']) && $form->isSubmitted() === $form['_submit']) {
			if (!$this->presenter->isAjax()) {
				$this->redirect('this', array('formName' => NULL, 'id' => NULL, 'mode' => NULL));
			}
			$this->formName = NULL;
			$this->id = NULL;
			$this->mode = NULL;
		}
	}


	public function navbarFormError()
	{
		$this->redrawControl('navbarForm');
	}


	public function actionFormError()
	{
		$this->redrawControl('actionForm');
	}


	public function render()
	{
		$this->onRender($this);

		if ($this->formName) {
			$this->template->form = $this->id ? $this->actionForms[$this->formName] : $this->navbarForms[$this->formName];
		}
		$this->template->showNavbar = $this->navbar;
		$this->template->render();
	}


	public function tableDelete($id, $action, $redirect = TRUE)
	{
		if (is_array($id)) {
			foreach ($id as $item) {
				$this->tableDelete($item, $action, FALSE);
			}
		} else {
			$this->dao->delete($this->dao->find($id));
		}

		if ($redirect) {
			$this->redirect('this');
		}
	}

}
