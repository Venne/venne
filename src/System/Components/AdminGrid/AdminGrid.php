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
use Kdyby\Doctrine\Entities\BaseEntity;
use Kdyby\Doctrine\EntityDao;
use Nette\Utils\Callback;
use Venne\Bridges\Kdyby\DoctrineForms\FormFactoryFactory;
use Venne\Forms\IFormFactory;
use Venne\System\Components\IGridoFactory;
use Venne\System\Components\INavbarControlFactory;
use Venne\System\Components\NavbarControl;
use Venne\System\Components\Section;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class AdminGrid extends \Venne\System\UI\Control
{

	const MODE_MODAL = 'modal';

	const MODE_PLACE = 'place';

	/**
	 * @var string
	 *
	 * @persistent
	 */
	public $id;

	/**
	 * @var string
	 *
	 * @persistent
	 */
	public $formName;

	/**
	 * @var string
	 *
	 * @persistent
	 */
	public $mode = self::MODE_MODAL;

	/** @var callable[] */
	public $onAttached;

	/** @var callable[] */
	public $onRender;

	/** @var \Kdyby\Doctrine\EntityDao */
	protected $dao;

	/** @var \Venne\System\Components\INavbarControlFactory */
	protected $navbarFactory;

	/** @var \Venne\System\Components\NavbarControl */
	protected $navbar;

	/** @var \Venne\System\Components\AdminGrid\Form[] */
	protected $navbarForms = array();

	/** @var \Venne\System\Components\AdminGrid\Form[] */
	protected $actionForms = array();

	/** @var \Venne\Bridges\Kdyby\DoctrineForms\FormFactoryFactory */
	private $formFactoryFactory;

	/** @var \Venne\System\Components\IGridoFactory */
	private $gridoFactory;

	public function __construct(
		EntityDao $dao = null,
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

	/**
	 * @param \Nette\ComponentModel\IContainer $presenter
	 */
	protected function attached($presenter)
	{
		parent::attached($presenter);

		$this->onAttached($this);

		if ($this->presenter->getParameter('do') === null && $this->presenter->isAjax()) {
			$this->redrawControl('table');
			$this->redrawControl('navbar');
			$this->redrawControl('breadcrumb');
		}
	}

	public function setDao(EntityDao $dao)
	{
		$this->dao = $dao;
	}

	/**
	 * @return \Kdyby\Doctrine\EntityDao
	 */
	public function getDao()
	{
		return $this->dao;
	}

	public function handleClose()
	{
		$this->redirect('this', array('id' => null, 'formName' => null));
	}

	/**
	 * @param \Venne\System\Components\AdminGrid\Form $form
	 * @param \Venne\System\Components\Section $section
	 * @param string $mode
	 * @return $this
	 */
	public function connectFormWithNavbar(Form $form, Section $section, $mode = self::MODE_MODAL)
	{
		$this->navbarForms[$section->getName()] = $form;

		$section->onClick[] = function ($section) use ($mode) {
			$this->redirect('this', array('id' => null, 'mode' => $mode, 'formName' => $section->getName()));
		};

		return $this;
	}

	/**
	 * @param \Venne\System\Components\AdminGrid\Form $form
	 * @param \Grido\Components\Actions\Event $action
	 * @param string $mode
	 * @return $this
	 */
	public function connectFormWithAction(Form $form, Event $action, $mode = self::MODE_MODAL)
	{
		$this->actionForms[$action->getName()] = $form;

		$action->onClick[] = function ($id, $action) use ($mode) {
			$this->redirect('this', array('id' => $id, 'mode' => $mode, 'formName' => $action->getName()));
		};

		return $this;
	}

	/**
	 * @param \Grido\Components\Actions\Event $action
	 * @return $this
	 */
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

	/**
	 * @param \Venne\Forms\IFormFactory $formFactory
	 * @param string $title
	 * @param callable $entityFactory
	 * @param string $type
	 * @return \Venne\System\Components\AdminGrid\Form
	 */
	public function createForm(IFormFactory $formFactory, $title, $entityFactory = null, $type = null)
	{
		return new Form($formFactory, $title, $entityFactory, $type);
	}

	/**
	 * @return \Nette\ComponentModel\IComponent
	 */
	public function getTable()
	{
		return $this['table'];
	}

	/**
	 * @return \Venne\System\Components\NavbarControl
	 */
	public function getNavbar()
	{
		if (!$this->navbar) {
			$this->navbar = $this['navbar'];
		}

		return $this->navbar;
	}

	public function setNavbar(NavbarControl $navbar = null)
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
	 * @return \Grido\Grid
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
	 * @return \Venne\System\Components\NavbarControl
	 */
	protected function createComponentNavbar()
	{
		$navbar = $this->navbarFactory ? $this->navbarFactory->create() : new NavbarControl();

		return $navbar;
	}

	/**
	 * @return \Venne\System\Components\AdminGrid\Form
	 */
	protected function createComponentNavbarForm()
	{
		$form = $this->navbarForms[$this->formName];

		if ($form->getEntityFactory()) {
			$entity = Callback::invoke($form->getEntityFactory());
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
				$this->formName = null;
				$this->mode = null;
				$this->redrawControl('navbarForm');
			}
		};
		$form->onSuccess[] = $this->navbarFormSuccess;
		$form->onError[] = $this->navbarFormError;

		if ($this->mode == self::MODE_PLACE) {
			$form->addSubmit('_cancel', 'Cancel')
				->setValidationScope(false)
				->onClick[] = function () {
				$this->redirect('this', array('formName' => null, 'mode' => null));
			};
		}

		return $form;
	}

	/**
	 * @return \Nette\Application\UI\Form
	 */
	protected function createComponentActionForm()
	{
		/** @var \Nette\Application\UI\Form $form */
		$form = $this->actionForms[$this->formName];
		$form = $this->formFactoryFactory
			->create($form->getFactory())
			->setEntity($this->getCurrentEntity())
			->create();

		$form->onSubmit[] = function () {
			if ($this->presenter->isAjax()) {
				$this->formName = null;
				$this->mode = null;
				$this->redrawControl('actionForm');
			}
		};
		$form->onSuccess[] = $this->actionFormSuccess;
		$form->onError[] = $this->actionFormError;

		if ($this->mode == self::MODE_PLACE) {
			$form->addSubmit('_cancel', 'Cancel')
				->setValidationScope(false)
				->onClick[] = function () {
				$this->redirect('this', array('formName' => null, 'mode' => null));
			};
		}

		return $form;
	}

	/**
	 * @return \Kdyby\Doctrine\Entities\BaseEntity
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
				$this->redirect('this', array('formName' => null, 'mode' => null));
			}
			$this->formName = null;
			$this->mode = null;
		}
	}

	public function actionFormSuccess(\Nette\Application\UI\Form $form)
	{
		if (isset($form['_submit']) && $form->isSubmitted() === $form['_submit']) {
			if (!$this->presenter->isAjax()) {
				$this->redirect('this', array('formName' => null, 'id' => null, 'mode' => null));
			}
			$this->formName = null;
			$this->id = null;
			$this->mode = null;
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

	/**
	 * @param integer $id
	 * @param integer[] $action
	 * @param bool $redirect
	 */
	public function tableDelete($id, $action, $redirect = true)
	{
		if (is_array($action)) {
			foreach ($action as $item) {
				$this->tableDelete($item, null, false);
			}
		} else {
			$this->dao->delete($this->dao->find($id));
		}

		if ($redirect) {
			$this->redirect('this');
		}
	}

}
