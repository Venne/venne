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
use Grido\Components\Operation;
use Grido\DataSources\Doctrine;
use Venne\Doctrine\Entities\BaseEntity;
use Kdyby\Doctrine\EntityRepository;
use Venne\Bridges\Kdyby\DoctrineForms\FormFactoryFactory;
use Venne\System\Components\IGridoFactory;
use Venne\System\Components\INavbarControlFactory;
use Venne\System\Components\NavbarControl;
use Venne\System\Components\Section;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class AdminGrid extends \Venne\System\UI\Control
{

	use \Venne\System\AjaxControlTrait;

	const MODE_MODAL = 'modal';

	const MODE_PLACE = 'place';

	/** @var string */
	private $id;

	/** @var string */
	private $formName;

	/** @var string */
	private $mode = self::MODE_MODAL;

	/** @var callable[] */
	public $onAttached;

	/** @var callable[] */
	public $onRender;

	/** @var callable[] */
	public $onClose;

	/** @var callable[] */
	public $onError;

	/** @var callable[] */
	public $onDelete;

	/** @var \Kdyby\Doctrine\EntityRepository */
	private $repository;

	/** @var \Venne\System\Components\INavbarControlFactory */
	private $navbarFactory;

	/** @var \Venne\System\Components\NavbarControl */
	private $navbar;

	/** @var \Venne\System\Components\AdminGrid\Form[] */
	private $forms = array();

	/** @var \Venne\System\Components\AdminGrid\Form[] */
	private $navbarForms = array();

	/** @var \Venne\System\Components\AdminGrid\Form[] */
	private $actionForms = array();

	/** @var \Venne\System\Components\IGridoFactory */
	private $gridoFactory;

	public function __construct(
		EntityRepository $repository,
		INavbarControlFactory $navbarFactory,
		FormFactoryFactory $formFactoryFactory,
		IGridoFactory $gridoFactory
	) {
		parent::__construct();

		$this->repository = $repository;
		$this->navbarFactory = $navbarFactory;
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

	public function setRepository(EntityRepository $repository)
	{
		$this->repository = $repository;
	}

	/**
	 * @return \Kdyby\Doctrine\EntityRepository
	 */
	public function getRepository()
	{
		return $this->repository;
	}

	public function handleReturn()
	{
		$this->id = null;
		$this->formName = null;
		$this->mode = null;
		$this->redirect('this');

		$this->redrawControl('formContainer');
		$this->redrawControl('table');
	}

	public function handleClose()
	{
		$this->id = null;
		$this->formName = null;
		$this->mode = null;
		$this->redirect('this');

		$this->redrawControl('formContainer');
		$this->redrawControl('table');
		$this->onClose($this);
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

		$section->onClick[] = function (Section $section) use ($mode) {
			$this->id = null;
			$this->formName = $section->getName();
			$this->mode = $mode;
			$this->redirect('this');

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

		$action->onClick[] = function ($id, Event $action) use ($mode) {
			$this->id = $id;
			$this->formName = $action->getName();
			$this->mode = $mode;
			$this->redirect('this');

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
		$action->onClick[] = function ($id) {
			$this->deleteItems($id);
			$this->onDelete($this, $id);

			$this->redirect('this');
			$this->redrawControl('table');
		};
		$action->setConfirm(function ($entity) {
			if (method_exists($entity, '__toString')) {
				return array('Really delete \'%s\'?', (string) $entity);
			}

			return 'Really delete?';
		});

		$this->getTable()->setOperation(array('delete' => 'Delete'), function (Operation $operation, $ids) {
			$this->deleteItems($ids);

			$this->redirect('this');
			$this->redrawControl('table');
		});

		return $this;
	}

	/**
	 * @param string $name
	 * @param string $title
	 * @param \Venne\Forms\IFormFactory|\Closure $formFactory
	 * @param string $type
	 * @return \Venne\System\Components\AdminGrid\Form
	 */
	public function addForm($name, $title, $formFactory, $type = null)
	{
		return $this->forms[$name] = new Form($formFactory, $title, $type);
	}

	/**
	 * @param string $name
	 * @return \Venne\System\Components\AdminGrid\Form
	 */
	public function getForm($name)
	{
		return $this->forms[$name];
	}

	/**
	 * @return \Grido\Grid
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

		if ($this->repository) {
			$grid->setModel(new Doctrine($this->repository->createQueryBuilder('a')));
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
		$formDefinition = $this->id !== null
			? $this->actionForms[$this->formName]
			: $this->navbarForms[$this->formName];

		$formFactory = $formDefinition->getFactory();

		if ($formFactory instanceof \Closure) {
			$formFactory = $formFactory($this->findCurrentEntity(), $formDefinition);
		}

		return $formFactory;
	}

	/**
	 * @return \Venne\Doctrine\Entities\BaseEntity|null
	 */
	public function findCurrentEntity()
	{
		return $this->id
			? $this->getRepository()->find($this->id)
			: null;
	}

	public function formSuccess()
	{
		if ($this->mode === self::MODE_PLACE) {
			$this->redrawControl('formContainer');
		}

		$this->id = null;
		$this->formName = null;
		$this->mode = null;
		$this->redirect('this');

		$this->redrawControl('form');
		$this->redrawControl('table');
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

		$this->template->mode = $this->mode;
		$this->template->formName = $this->formName;

		parent::render();
	}

	/**
	 * @param int|int[] $ids
	 */
	private function deleteItems($ids)
	{
		if (is_array($ids)) {
			foreach ($ids as $id) {
				$this->deleteItems($id);
			}
		} else {
			try {
				$this->repository->getEntityManager()->remove($this->repository->find($ids));
				$this->repository->getEntityManager()->flush();
			} catch (\Exception $e) {
				$this->onError($this, $e);
			}
		}
	}

	public function loadState(array $params)
	{
		parent::loadState($params);

		$this->id = isset($params['id']) ? $params['id'] : null;
		$this->formName = isset($params['formName']) ? $params['formName'] : null;
		$this->mode = isset($params['mode']) ? $params['mode'] : self::MODE_MODAL;
	}

	public function saveState(array & $params, $reflection = null)
	{
		parent::saveState($params, $reflection);

		$params['id'] = $this->id;
		$params['formName'] = $this->formName;
		$params['mode'] = $this->mode;
	}

}
