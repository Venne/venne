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

use Venne;
use CmsModule\Content\Repositories\PageRepository;
use DoctrineModule\Repositories\BaseRepository;
use CmsModule\Content\ContentManager;
use CmsModule\Content\Forms\BasicFormFactory;
use CmsModule\Content\Forms\RoutesFormFactory;
use CmsModule\Content\Forms\SpecialFormFactory;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 *
 * @secured
 */
class ContentPresenter extends BasePresenter
{


	/** @persistent */
	public $key;

	/** @persistent */
	public $type;

	/** @persistent */
	public $section;

	/** @var PageRepository */
	protected $pageRepository;

	/** @var BaseRepository */
	protected $languageRepository;

	/** @var BaseRepository */
	protected $pageTagRepository;

	/** @var ContentManager */
	protected $contentManager;

	/** @var BasicFormFactory */
	protected $contentFormFactory;

	/** @var RoutesFormFactory */
	protected $contentRoutesFormFactory;

	/** @var SpecialFormFactory */
	protected $specialFormFactory;


	/**
	 * @param PageRepository $pageRepository
	 * @param BaseRepository $languageRepository
	 * @param BaseRepository $pageTagRepository
	 * @param ContentManager $contentManager
	 */
	function __construct(PageRepository $pageRepository, BaseRepository $languageRepository, BaseRepository $pageTagRepository, ContentManager $contentManager)
	{
		$this->pageRepository = $pageRepository;
		$this->languageRepository = $languageRepository;
		$this->pageTagRepository = $pageTagRepository;
		$this->contentManager = $contentManager;
	}


	/**
	 * @param BasicFormFactory $contentForm
	 */
	public function injectContentForm(BasicFormFactory $contentForm)
	{
		$this->contentFormFactory = $contentForm;
	}


	/**
	 * @param RoutesFormFactory $routesForm
	 */
	public function injectRoutesForm(RoutesFormFactory $routesForm)
	{
		$this->contentRoutesFormFactory = $routesForm;
	}


	/**
	 * @param SpecialFormFactory $specialFormFactory
	 */
	public function injectSpecialFormFactory(SpecialFormFactory $specialFormFactory)
	{
		$this->specialFormFactory = $specialFormFactory;
	}


	/**
	 * @secured(privilege="show")
	 */
	public function actionDefault()
	{
	}


	/**
	 * @secured(privilege="show")
	 */
	public function actionSpecial()
	{
		if (!$this->getApplication()->catchExceptions) {
			$this->flashMessage('Capturing error pages will not work. Please enable catch exceptions in application settings.', 'info', true);
		}
	}


	public function handleDelete($id)
	{
		$entity = $this->pageRepository->find($id);
		$link = $this->link('this', array('key' => $entity->translationFor->id));

		$this->pageRepository->delete($entity);
		$this->flashMessage('Translation has been removed', 'success');
		$this->redirectUrl($link);
	}


	public function createComponentTable()
	{
		$presenter = $this;

		$table = new \CmsModule\Components\Table\TableControl;
		$table->setRepository($this->pageRepository);
		$table->setPaginator(10);
		$table->enableSorter();
		$table->setDql(function (\Doctrine\ORM\QueryBuilder $builder) {
			$builder->andWhere('a.translationFor IS NULL');
		});

		// columns
		$table->addColumn('name', 'Name', '50%');
		$table->addColumn('url', 'URL', '20%', function ($entity) {
			return $entity->mainRoute->url;
		});
		$table->addColumn('languages', 'Languages', '30%', function ($entity) {
			$ret = implode(", ", $entity->languages->toArray());
			foreach ($entity->translations as $translation) {
				$ret .= ', ' . implode(", ", $translation->languages->toArray());
			}
			return $ret;
		});

		// actions
		$table->addAction('edit', 'Edit')->onClick[] = function ($button, $entity) use ($presenter) {
			if (!$presenter->isAjax()) {
				$presenter->redirect('edit', array('key' => $entity->id));
			}
			$presenter->invalidateControl('content');
			$presenter->payload->url = $presenter->link('edit', array('key' => $entity->id));
			$presenter->setView('edit');
			$presenter->changeAction('edit');
			$presenter->key = $entity->id;
		};
		$table->addActionDelete('delete', 'Delete')->onSuccess[] = function () use ($presenter) {
			$presenter['panel']->invalidateControl('content');
		};

		// global actions
		$table->setGlobalAction($table['delete']);

		return $table;
	}


	public function createComponentSpecialTable()
	{
		$presenter = $this;

		$table = new \CmsModule\Components\Table\TableControl;
		$table->setTemplateConfigurator($this->templateConfigurator);
		$table->setRepository($this->pageTagRepository);
		$table->setPaginator(10);

		// forms
		$form = $table->addForm($this->specialFormFactory, 'Special form');

		// navbar
		$table->addButtonCreate('create', 'Create new', $form, 'file');

		// columns
		$table->addColumn('tag', 'Tag', '40%', function ($entity) {
			$tags = \CmsModule\Content\Entities\PageTagEntity::getTags();
			return $tags[$entity->tag];
		});
		$table->addColumn('page', 'Page', '60%', function ($entity) {
			return ($entity->page ? (string)$entity->page : '');
		});

		// actions
		$table->addActionEdit('edit', 'Edit', $form);
		$table->addActionDelete('delete', 'Delete');

		// global actions
		$table->setGlobalAction($table['delete']);

		return $table;
	}


	public function createComponentForm()
	{
		$contentType = $this->contentManager->getContentType($this->getParameter("type"));
		$entity = $this->pageRepository->createNewByEntityName($contentType->getEntityName());

		$form = $this->contentFormFactory->invoke($entity);
		$form->onSuccess[] = $this->formSuccess;
		return $form;
	}


	public function formSuccess($form)
	{
		$this->flashMessage("Page has been created");

		if (!$this->isAjax()) {
			$this->redirect('edit', array('type' => null, 'key' => $form->data->id));
		}
		$this->invalidateControl('content');
		$this['panel']->invalidateControl('content');
		$this->payload->url = $this->link('edit', array('type' => null, 'key' => $form->data->id));
		$this->setView('edit');
		$this->changeAction('edit');
		$this->key = $form->data->id;

		// refresh left panel
		$this['panel']->invalidateControl('content');
	}


	public function createComponentFormTranslate()
	{
		$pageEntity = $this->pageRepository->find($this->getParameter("key"));
		$contentType = $this->contentManager->getContentType($pageEntity::getType());

		/** @var $entity \CmsModule\Entities\PageEntity */
		$entity = $this->pageRepository->createNewByEntityName($contentType->getEntityName());
		$entity->setTranslationFor($pageEntity);

		$form = $this->contentFormFactory->invoke($entity);
		$form->onSuccess[] = $this->formSuccess;
		return $form;
	}


	public function createComponentFormEdit()
	{
		$repository = $this->pageRepository;
		$entity = $repository->find($this->getParameter("key"));
		$contentType = $this->contentManager->getContentType($entity->type);

		if ((!$this->section && count($contentType->sections) == 0) || $this->section == 'basic') {
			$form = $this->contentFormFactory->invoke($entity);
		} elseif ($this->section == 'routes') {
			$form = $this->contentRoutesFormFactory->invoke($entity);
		} else {
			if ($this->section) {
				if (!$contentType->hasSection($this->section)) {
					throw new \Nette\Application\UI\InvalidLinkException("Section '$this->section' not exists");
				}
				$formFactory = $contentType->sections[$this->section]->getFormFactory();
			} else {
				$sections = $contentType->sections;
				$formFactory = reset($sections)->getFormFactory();
			}
			$form = $formFactory->invoke($entity);
		}

		if ($form instanceof \CmsModule\Content\ISectionControl) {
			$form->setEntity($entity);
		} else if ($form instanceof \Venne\Forms\Form) {
			$form->onSuccess[] = $this->formEditSuccess;
		} else {
			throw new \Nette\InvalidArgumentException("Control must be instance of '\Venne\Forms\Form' OR 'CmsModule\Content\ISectionControl'. " . get_class($form) . " is given");
		}
		return $form;
	}


	public function formEditSuccess($form)
	{
		$this->flashMessage("Page has been updated");

		if (!$this->isAjax()) {
			$this->redirect("this");
		}
	}


	public function renderEdit()
	{
		$this->invalidateControl('content');
		$this->invalidateControl('toolbar');

		$this->template->entity = $this->pageRepository->find($this->key);
		$this->template->contentType = $this->contentManager->getContentType($this->template->entity->type);
		$sections = $this->template->contentType->getSections();
		$this->template->section = $this->section ? : reset($sections)->name;
	}
}
