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

		if ($this->presenter->getParameter('do') === null) {
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
		$this->redirect('this', array(
			'formName' => null,
			'id' => null,
			'mode' => null,
		));

		$this->redrawControl('table');
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
			$this->redirect('this', array(
				'formName' => $section->getName(),
				'id' => null,
				'mode' => $mode,
			));

			$this->redrawControl('table');
			$this->redrawControl('formContainer');
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
			$this->redirect('this', array(
				'formName' => $action->getName(),
				'id' => $id,
				'mode' => $mode,
			));

			$this->redrawControl('table');
			$this->redrawControl('formContainer');
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
	 * @return \Nette\Application\UI\Form
	 */
	protected function createComponentForm()
	{
		/** @var \Nette\Application\UI\Form $form */
		$form = $this->id !== null
			? $this->actionForms[$this->formName]
			: $this->navbarForms[$this->formName];
		$form = $this->formFactoryFactory
			->create($form->getFactory())
			->setEntity($this->getCurrentEntity())
			->create();

		$form->onSubmit[] = function () {
			$this->redrawControl('form');
		};
		$form->onSuccess[] = $this->formSuccess;
		$form->onError[] = $this->formError;

		if ($this->mode == self::MODE_PLACE) {
			$form->addSubmit('_cancel', 'Cancel')
				->setValidationScope(false)
				->onClick[] = function () {
				$this->redirect('this', array(
					'formName' => null,
					'id' => null,
					'mode' => null,
				));
				$this->redrawControl('form');
			};
		}

		return $form;
	}

	/**
	 * @return \Kdyby\Doctrine\Entities\BaseEntity
	 */
	public function getCurrentEntity()
	{
		$form = $this->id !== null
			? $this->actionForms[$this->formName]
			: $this->navbarForms[$this->formName];

		if (is_callable($form->getEntityFactory())) {
			return Callback::invoke($form->getEntityFactory());
		}

		if ($this->id !== null) {
			return $this->getDao()->find($this->id);
		}

		$class = $this->dao->getClassName();

		return new $class;
	}

	public function formSuccess(\Nette\Application\UI\Form $form)
	{
		if (isset($form['_submit']) && $form->isSubmitted() === $form['_submit']) {
			$this->redirect('this', array(
				'formName' => null,
				'id' => null,
				'mode' => null,
			));

			$this->redrawControl('table');
		}
	}

	public function formError()
	{
		$this->redrawControl('form');
	}

	public function render()
	{
		$this->onRender($this);

		if ($this->formName) {
			$this->template->form = $this->id
				? $this->actionForms[$this->formName]
				: $this->navbarForms[$this->formName];
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

		$this->redirect('this');
		$this->redrawControl('table');
	}

}
