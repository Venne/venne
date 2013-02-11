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

use CmsModule\Content\Entities\LayoutEntity;
use Venne;
use CmsModule\Content\Forms\LayoutFormFactory;
use CmsModule\Content\ElementManager;
use CmsModule\Content\Elements\Forms\BasicFormFactory;
use CmsModule\Content\Entities\ElementEntity;
use CmsModule\Content\LayoutManager;
use DoctrineModule\Repositories\BaseRepository;

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

	/** @var Venne\Module\Helpers */
	protected $moduleHelpers;

	/** @var BaseRepository */
	protected $layoutRepository;

	/** @var BaseRepository */
	protected $elementRepository;

	/** @var LayoutFormFactory */
	protected $layoutFormFactory;

	/** @var BasicFormFactory */
	protected $basicFormFactory;

	/** @var LayoutEntity */
	protected $currentLayout;


	public function __construct(BaseRepository $layoutRepository, BaseRepository $elementRepository, Venne\Module\Helpers $moduleHelpers)
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
			$file = $this->moduleHelpers->expandPath($this->currentLayout->file, 'Resources');

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


	/**
	 * @secured
	 */
	public function handleCreate($id)
	{
		if (!$this->isAjax()) {
			$this['table-navbar']->redirect('click!', array('id' => $id));
		}

		$this->invalidateControl('content');
		$this['table-navbar']->handleClick($id);

		$this->payload->url = $this['table-navbar']->link('click!', array('id' => $id));
	}


	public function createComponentTable()
	{
		$_this = $this;
		$table = new \CmsModule\Components\Table\TableControl;
		$table->setTemplateConfigurator($this->templateConfigurator);
		$table->setRepository($this->layoutRepository);

		// forms
		$form = $table->addForm($this->layoutFormFactory, 'Layout');

		// navbar
		if ($this->isAuthorized('create')) {
			$table->addButtonCreate('create', 'Create new', $form, 'file');
		}

		// columns
		$table->addColumn('name', 'Name')
			->setWidth('60%');
		$table->addColumn('file', 'File')
			->setWidth('40%');

		// actions
		if ($this->isAuthorized('edit')) {
			$table->addActionEdit('edit', 'Edit', $form);
			$table->addAction('elements', 'Elements')->onClick[] = function ($button, $entity) use ($_this) {
				if (!$_this->isAjax()) {
					$_this->redirect('this', array('key' => $entity->id));
				}
				$_this->invalidateControl('content');
				$_this->payload->url = $_this->link('this', array('key' => $entity->id));
				$_this->key = $entity->id;
			};
		}

		if ($this->isAuthorized('remove')) {
			$table->addActionDelete('delete', 'Delete');

			// global actions
			$table->setGlobalAction($table['delete']);
		}

		return $table;
	}


	public function createComponentElementTable()
	{
		$table = new \CmsModule\Components\Table\TableControl;
		$table->setTemplateConfigurator($this->templateConfigurator);
		$table->setRepository($this->elementRepository);

		$parent = $this->key;
		$table->setDql(function (\Doctrine\ORM\QueryBuilder $a) use ($parent) {
			if (!$parent) {
				$a->andWhere('a.layout IS NULL');
			} else {
				$a->andWhere('a.layout = :id')->setParameter('id', $parent);
			}
		});

		// forms
		$form = $table->addForm($this->basicFormFactory, 'Element');

		// columns
		$table->addColumn('nameRaw', 'Name')
			->setWidth('35%');
		$table->addColumn('mode', 'Mode')
			->setWidth('15%')
			->setCallback(function ($entity) {
				$modes = ElementEntity::getModes();
				return $modes[$entity->mode];
			});
		$table->addColumn('page', 'Page')
			->setWidth('25%');
		$table->addColumn('route', 'Route')
			->setWidth('25%');

		// actions
		$table->addActionEdit('edit', 'Edit', $form);
		$table->addActionDelete('delete', 'Delete');

		// global actions
		$table->setGlobalAction($table['delete']);

		return $table;
	}


	public function renderDefault()
	{
		$this->template->layoutRepository = $this->layoutRepository;
		$this->template->elementRepository = $this->elementRepository;
	}
}
