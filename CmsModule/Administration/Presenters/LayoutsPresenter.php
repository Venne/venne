<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace CmsModule\Administration\Presenters;

use CmsModule\Administration\Components\AdminGrid\AdminGrid;
use CmsModule\Content\Entities\LayoutEntity;
use CmsModule\Content\Repositories\ElementRepository;
use CmsModule\Content\Repositories\LayoutRepository;
use CmsModule\Content\Forms\LayoutFormFactory;
use CmsModule\Content\ElementManager;
use CmsModule\Content\Elements\Forms\BasicFormFactory;
use CmsModule\Content\Entities\ElementEntity;
use CmsModule\Content\LayoutManager;
use Grido\Components\Filters\Filter;
use Grido\DataSources\Doctrine;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 *
 * @secured
 */
class LayoutsPresenter extends BasePresenter
{

	/** @persistent */
	public $key;

	/** @var LayoutManager */
	protected $layoutManager;

	/** @var \Venne\Module\Helpers */
	protected $moduleHelpers;

	/** @var LayoutRepository */
	protected $layoutRepository;

	/** @var ElementRepository */
	protected $elementRepository;

	/** @var LayoutFormFactory */
	protected $layoutFormFactory;

	/** @var BasicFormFactory */
	protected $basicFormFactory;

	/** @var LayoutEntity */
	protected $currentLayout;


	public function __construct(LayoutRepository $layoutRepository, ElementRepository $elementRepository, \Venne\Module\Helpers $moduleHelpers)
	{
		$this->layoutRepository = $layoutRepository;
		$this->elementRepository = $elementRepository;
		$this->moduleHelpers = $moduleHelpers;
	}


	/**
	 * @param \CmsModule\Content\Forms\LayoutFormFactory $layoutFormFactory
	 */
	public function injectLayoutFormFactory(LayoutFormFactory $layoutFormFactory)
	{
		$this->layoutFormFactory = $layoutFormFactory;
	}


	/**
	 * @param \CmsModule\Content\LayoutManager $layoutManager
	 */
	public function injectLayoutManager(LayoutManager $layoutManager)
	{
		$this->layoutManager = $layoutManager;
	}


	/**
	 * @param \CmsModule\Content\Elements\Forms\BasicFormFactory $basicFormFactory
	 */
	public function injectBasicFormFactory(BasicFormFactory $basicFormFactory)
	{
		$this->basicFormFactory = $basicFormFactory;
	}


	public function startup()
	{
		parent::startup();

		if ($this->key) {
			$this->currentLayout = $this->layoutRepository->find($this->key);
			$file = $this->moduleHelpers->expandPath($this->currentLayout->file, 'Resources/layouts');

			foreach ($this->layoutManager->getElementsByFile($file) as $key => $type) {
				if ($this->elementRepository->findOneBy(array('layout' => $this->currentLayout->id, 'nameRaw' => $key))) {
					continue;
				}

				$component = $this->context->cms->elementManager->createInstance($type);
				$component->setLayout($this->currentLayout);
				$component->setName($key);
				$component->getEntity();
			}
		}
	}


	/**
	 * @secured(privilege="show")
	 */
	public function actionDefault()
	{
	}


	/**
	 * @secured
	 */
	public function actionCreate()
	{
	}


	/**
	 * @secured
	 */
	public function actionEdit()
	{
	}


	/**
	 * @secured
	 */
	public function actionRemove()
	{
	}


	public function handleCreate()
	{
		$this['table']['navbar']->{'handleClick'}('navbar-new');
		$this->invalidateControl('content');
	}


	protected function createComponentTable()
	{
		$admin = new AdminGrid($this->layoutRepository);

		// columns
		$table = $admin->getTable();
		$table->setTranslator($this->context->translator->translator);
		$table->addColumn('name', 'Name')
			->setSortable()
			->setFilter()->setSuggestion();
		$table->getColumn('name')->getCellPrototype()->width = '60%';

		$table->addColumn('file', 'File')
			->setSortable()
			->setFilter()->setSuggestion();
		$table->getColumn('file')->getCellPrototype()->width = '40%';

		// actions
		if ($this->isAuthorized('edit')) {
			$table->addAction('edit', 'Edit')
				->getElementPrototype()->class[] = 'ajax';

			$table->addAction('elements', 'Elements')
				->getElementPrototype()->class[] = 'ajax';

			$form = $admin->createForm($this->layoutFormFactory, 'Layout');
			$admin->connectFormWithAction($form, $table->getAction('edit'));

			// Toolbar
			$toolbar = $admin->getNavbar();
			$toolbar->addSection('new', 'Create', 'file');
			$admin->connectFormWithNavbar($form, $toolbar->getSection('new'));

			$admin->connectActionWithFloor($table->getAction('elements'), $this['elementTable'], 'Borec');
		}

		if ($this->isAuthorized('remove')) {
			$table->addAction('delete', 'Delete')
				->getElementPrototype()->class[] = 'ajax';
			$admin->connectActionAsDelete($table->getAction('delete'));
		}

		return $admin;
	}


	protected function createComponentElementTable()
	{
		$admin = new AdminGrid($this->elementRepository);

		// columns
		$table = $admin->getTable();
		$table->setTranslator($this->context->translator->translator);
		$table->addColumn('nameRaw', 'Name')
			->setSortable()
			->setFilter()->setSuggestion();
		$table->getColumn('nameRaw')->getCellPrototype()->width = '23%';

		$table->addColumn('mode', 'Mode')
			->setSortable()
			->setCustomRender(function ($entity) {
				$modes = ElementEntity::getModes();
				return $modes[$entity->mode];
			})
			->getCellPrototype()->width = '12%';

		$table->addColumn('langMode', 'Language mode')
			->setSortable()
			->setCustomRender(function ($entity) {
				$modes = ElementEntity::getLangModes();
				return $modes[$entity->langMode];
			})
			->getCellPrototype()->width = '12%';

		$table->addColumn('page', 'Page')
			->setSortable()
			->getCellPrototype()->width = '20%';

		$table->addColumn('route', 'Route')
			->setSortable()
			->getCellPrototype()->width = '20%';

		$table->addColumn('language', 'Language')
			->setSortable()
			->getCellPrototype()->width = '15%';

		// filters
		$table->addFilter('mode', 'Mode', Filter::TYPE_SELECT, array('' => '') + ElementEntity::getModes());
		$table->addFilter('langMode', 'Mode', Filter::TYPE_SELECT, array('' => '') + ElementEntity::getLangModes());

		// actions
		if ($this->isAuthorized('edit')) {
			$table->addAction('edit', 'Edit')
				->getElementPrototype()->class[] = 'ajax';

			$form = $admin->createForm($this->basicFormFactory, 'Element');
			$admin->connectFormWithAction($form, $table->getAction('edit'));
		}

		if ($this->isAuthorized('remove')) {
			$table->addAction('delete', 'Delete')
				->getElementPrototype()->class[] = 'ajax';
			$admin->connectActionAsDelete($table->getAction('delete'));
		}

		$admin->onRender[] = function (AdminGrid $admin) {
			$qb = $admin->getRepository()->createQueryBuilder('a');
			if (!$admin->parentFloor->floorId) {
				$qb = $qb->andWhere('a.layout IS NULL');
			} else {
				$qb = $qb->andWhere('a.layout = :id')->setParameter('id', $admin->getParentFloor()->floorId);
			}
			$admin->getTable()->setModel(new Doctrine($qb));
		};

		return $admin;
	}


	public function renderDefault()
	{
		$this->template->layoutRepository = $this->layoutRepository;
		$this->template->elementRepository = $this->elementRepository;
	}
}
