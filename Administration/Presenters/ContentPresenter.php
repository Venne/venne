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
use DoctrineModule\ORM\BaseRepository;
use CmsModule\Content\ContentManager;
use Nette\Callback;

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

	/** @var Callback */
	protected $contentFormFactory;

	/** @var Callback */
	protected $contentRoutesFormFactory;


	/**
	 * @param \CmsModule\Content\Repositories\PageRepository $pageRepository
	 * @param \DoctrineModule\ORM\BaseRepository $languageRepository
	 * @param \CmsModule\Content\ContentManager $contentManager
	 * @param \Nette\Callback $contentFormFactory
	 * @param \Nette\Callback $contentRoutesFormFactory
	 */
	function __construct(PageRepository $pageRepository, BaseRepository $languageRepository, ContentManager $contentManager, Callback $contentFormFactory, Callback $contentRoutesFormFactory)
	{
		$this->pageRepository = $pageRepository;
		$this->languageRepository = $languageRepository;
		$this->contentManager = $contentManager;
		$this->contentFormFactory = $contentFormFactory;
		$this->contentRoutesFormFactory = $contentRoutesFormFactory;
	}


	public function createComponentTable()
	{
		$table = new \CmsModule\Components\TableControl;
		$table->setRepository($this->pageRepository);
		$table->setPaginator(10);
		$table->enableSorter();
		$table->setDql(function(\Doctrine\ORM\QueryBuilder $builder)
		{
			$builder->andWhere('a.translationFor IS NULL');
		});

		$table->addColumn('name', 'Name', '50%');
		$table->addColumn('url', 'URL', '20%', function($entity)
		{
			return $entity->mainRoute->url;
		});
		$table->addColumn('languages', 'Languages', '30%', function($entity)
		{
			$ret = implode(", ", $entity->languages->toArray());
			foreach ($entity->translations as $translation) {
				$ret .= ', ' . implode(", ", $translation->languages->toArray());
			}
			return $ret;
		});

		$presenter = $this;
		$table->addAction('edit', 'Edit', function($entity) use ($presenter)
		{
			if (!$presenter->isAjax()) {
				$presenter->redirect('edit', array('key' => $entity->id));
			}
			$presenter->payload->url = $presenter->link('edit', array('key' => $entity->id));
			$presenter->setView('edit');
			$presenter->changeAction('edit');
			$presenter->key = $entity->id;
		});
		$table->addAction('delete', 'Delete', function($entity) use ($presenter)
		{
			$presenter->delete($entity);
			if (!$presenter->isAjax()) {
				$presenter->redirect('default', array('key' => NULL));
			}
			$presenter->payload->url = $presenter->link('default', array('id' => NULL));
		});

		$table->addGlobalAction('delete', 'Delete', function($entity) use ($presenter)
		{
			$presenter->delete($entity);
		});

		return $table;
	}


	public function createComponentForm()
	{
		$repository = $this->pageRepository;
		$contentType = $this->contentManager->getContentType($this->getParameter("type"));
		$entity = $repository->createNewByEntityName($contentType->getEntityName());

		$form = $this->contentFormFactory->invoke();
		$form->setEntity($entity);
		$form->onSuccess[] = $this->formSuccess;
		return $form;
	}


	public function formSuccess($form)
	{
		$repository = $this->pageRepository;

		if ($repository->isUnique($form->entity)) {
			$repository->save($form->entity);

			$this->flashMessage("Page has been created");

			if (!$this->isAjax()) {
				$this->redirect('edit', array('type' => null, 'key' => $form->entity->id));
			}
			$this['panel']->invalidateControl('content');
			$this->payload->url = $this->link('edit', array('type' => null, 'key' => $form->entity->id));
			$this->setView('edit');
			$this->changeAction('edit');
			$this->key = $form->entity->id;
		} else {
			$this->flashMessage("URL is not unique", "warning");
		}
	}


	public function createComponentFormTranslate()
	{
		$repository = $this->pageRepository;
		$pageEntity = $repository->find($this->getParameter("key"));
		$contentType = $this->contentManager->getContentType($pageEntity::getType());

		/** @var $entity \CmsModule\Entities\PageEntity */
		$entity = $this->pageRepository->createNewByEntityName($contentType->getEntityName());
		$entity->setTranslationFor($pageEntity);

		$form = $this->contentFormFactory->invoke();
		$form->setEntity($entity);
		$form->onSuccess[] = $this->formTranslateSuccess;
		return $form;
	}


	public function formTranslateSuccess($form)
	{
		$this->formSuccess($form);
	}


	public function createComponentFormEdit()
	{
		$repository = $this->pageRepository;
		$entity = $repository->find($this->getParameter("key"));
		$contentType = $this->contentManager->getContentType($entity->type);

		if ((!$this->section && count($contentType->sections) == 0) || $this->section == 'basic') {
			$form = $this->contentFormFactory->invoke();
		} elseif ($this->section == 'routes') {
			$form = $this->contentRoutesFormFactory->invoke();
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
			$form = $formFactory->invoke();
		}

		$form->setEntity($entity);

		if ($form instanceof \Nette\Forms\Form) {
			$form->onSuccess[] = $this->formEditSuccess;
		}
		return $form;
	}


	public function formEditSuccess($form)
	{
		$repository = $this->pageRepository;

		if ($repository->isUnique($form->entity)) {
			$repository->save($form->entity);

			$this->flashMessage("Page has been updated");

			if (!$this->isAjax()) {
				$this->redirect("this");
			}
		} else {
			$this->flashMessage("URL is not unique", "warning");
		}
	}


	public function delete($entity)
	{
		$this->pageRepository->delete($entity);
		$this->flashMessage("Page has been deleted", "success");

		if (!$this->isAjax()) {
			$this->redirect("this", array("key" => NULL));
		}
	}


	public function renderEdit()
	{
		$this->template->entity = $this->pageRepository->find($this->getParameter("key"));
		$this->template->contentType = $this->contentManager->getContentType($this->template->entity->type);
		$sections = $this->template->contentType->getSections();
		$this->template->section = $this->section ? : reset($sections)->name;
	}
}
