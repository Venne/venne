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

	/** @var ContentManager */
	protected $contentManager;

	/** @var BasicFormFactory */
	protected $contentFormFactory;

	/** @var RoutesFormFactory */
	protected $contentRoutesFormFactory;


	/**
	 * @param \CmsModule\Content\Repositories\PageRepository $pageRepository
	 * @param \DoctrineModule\Repositories\BaseRepository $languageRepository
	 * @param \CmsModule\Content\ContentManager $contentManager
	 */
	function __construct(PageRepository $pageRepository, BaseRepository $languageRepository, ContentManager $contentManager)
	{
		$this->pageRepository = $pageRepository;
		$this->languageRepository = $languageRepository;
		$this->contentManager = $contentManager;
	}


	public function injectContentForm(BasicFormFactory $contentForm)
	{
		$this->contentFormFactory = $contentForm;
	}


	public function injectRoutesForm(RoutesFormFactory $routesForm)
	{
		$this->contentRoutesFormFactory = $routesForm;
	}


	/**
	 * @secured(privilege="show")
	 */
	public function actionDefault()
	{
	}


	/**
	 * @secured(privilege="create")
	 */
	public function actionCreate()
	{
	}


	/**
	 * @secured(privilege="edit")
	 */
	public function actionEdit()
	{
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
		$this['panel']->invalidateControl('content');
		$this->payload->url = $this->link('edit', array('type' => null, 'key' => $form->data->id));
		$this->setView('edit');
		$this->changeAction('edit');
		$this->key = $form->data->id;
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
		$this->template->entity = $this->pageRepository->find($this->key);
		$this->template->contentType = $this->contentManager->getContentType($this->template->entity->type);
		$sections = $this->template->contentType->getSections();
		$this->template->section = $this->section ? : reset($sections)->name;
	}
}
