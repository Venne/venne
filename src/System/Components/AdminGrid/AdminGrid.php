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

use DoctrineModule\Repositories\BaseRepository;
use Grido\DataSources\Doctrine;
use Kdyby\Doctrine\Entities\BaseEntity;
use Kdyby\Doctrine\EntityDao;
use Nette\Callback;
use Venne\Bridges\Kdyby\DoctrineForms\FormFactoryFactory;
use Venne\Forms\IFormFactory;
use Venne\System\Components\Grido\Actions\CallbackAction;
use Venne\System\Components\Grido\Grid;
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
	public $floor;

	/**
	 * @var string
	 * @persistent
	 */
	public $floorId;

	/**
	 * @var string
	 * @persistent
	 */
	public $mode = self::MODE_MODAL;

	/** @var array */
	public $onAttached;

	/** @var array */
	public $onRender;

	/** @var AdminGrid[] */
	protected $floors = array();

	/** @var AdminGrid */
	protected $parentFloor;

	/** @var BaseRepository */
	protected $dao;

	/** @var Callback */
	protected $tableFactory;

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


	/**
	 * @param EntityDao $dao
	 * @param INavbarControlFactory $navbarFactory
	 * @param FormFactoryFactory $formFactoryFactory
	 */
	public function __construct(
		EntityDao $dao = NULL,
		INavbarControlFactory $navbarFactory,
		FormFactoryFactory $formFactoryFactory
	)
	{
		parent::__construct();

		$this->dao = $dao;
		$this->navbarFactory = $navbarFactory;
		$this->formFactoryFactory = $formFactoryFactory;
	}


	protected function attached($presenter)
	{
		parent::attached($presenter);

		$this->onAttached($this);
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
		$this->invalidateControl('navbarFormContainer');
		$this->invalidateControl('actionFormContainer');
		$this->presenter->payload->url = $this->link('this');
	}


	/**
	 * @param Form $form
	 * @param Section $section
	 * @return $this
	 */
	public function connectFormWithNavbar(Form $form, Section $section, $mode = self::MODE_MODAL)
	{
		$this->navbarForms[$section->getName()] = $form;

		$_this = $this;
		$section->onClick[] = function ($section) use ($_this, $mode) {
			$_this->mode = $mode;
			$_this->id = NULL;
			$_this->invalidateControl('navbarFormContainer');
			if ($_this->mode === $_this::MODE_PLACE) {
				$_this->invalidateControl('table');
				$_this->invalidateControl('navbarFormContainer');
				$_this->invalidateControl('actionFormContainer');
			}
			$_this->setFormName($section->getName());
		};
		return $this;
	}


	public function connectFormWithAction(Form $form, CallbackAction $action, $mode = self::MODE_MODAL)
	{
		$this->actionForms[$action->getName()] = $form;

		$_this = $this;
		$action->onClick[] = function ($action, $id) use ($_this, $mode) {
			$_this->mode = $mode;
			$_this->id = $id;
			$_this->invalidateControl('actionFormContainer');
			if ($_this->mode === $_this::MODE_PLACE) {
				$_this->invalidateControl('table');
				$_this->invalidateControl('navbarFormContainer');
				$_this->invalidateControl('actionFormContainer');
			}
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

		$this->getTable()->setOperation(array('delete' => 'Delete'), $this->tableDelete);
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
	 * @return AdminGrid
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


	public function createForm(IFormFactory $formFactory, $title, $entityFactory = NULL, $type = NULL)
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
			if ($this->dao) {
				$grid->setModel(new Doctrine($this->dao->createQueryBuilder('a')));
			}
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
		$form = $this->actionForms[$this->formName];
		$form = $this->formFactoryFactory
			->create($form->getFactory())
			->setEntity($this->getCurrentEntity())
			->create();

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
		$this->invalidateControl('navbarForm');

		if (isset($form['_submit']) && $form->isSubmitted() === $form['_submit']) {

			$this->formName = NULL;
			if (!$this->presenter->isAjax()) {
				$this->redirect('this', array('formName' => NULL, 'mode' => NULL));
			}

			$this->invalidateControl('table');
			if ($this->mode === $this::MODE_PLACE) {
				$this->invalidateControl('navbarFormContainer');
				$this->invalidateControl('actionFormContainer');
			}
			$this->presenter->payload->url = $this->link('this', array('formName' => NULL, 'mode' => NULL));
		}
	}


	public function actionFormSuccess(\Nette\Application\UI\Form $form)
	{
		$this->invalidateControl('actionForm');

		if (isset($form['_submit']) && $form->isSubmitted() === $form['_submit']) {

			$this->id = NULL;
			$this->formName = NULL;
			if (!$this->presenter->isAjax()) {
				$this->redirect('this', array('formName' => NULL, 'id' => NULL, 'mode' => NULL));
			}

			$this->invalidateControl('table');
			if ($this->mode === $this::MODE_PLACE) {
				$this->invalidateControl('navbarFormContainer');
				$this->invalidateControl('actionFormContainer');
			}
			$this->presenter->payload->url = $this->link('this', array('formName' => NULL, 'id' => NULL, 'mode' => NULL));
		}
	}


	public function navbarFormError()
	{
		$this->invalidateControl('navbarForm');
	}


	public function actionFormError()
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
			$this->dao->delete($this->dao->find($id));
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
