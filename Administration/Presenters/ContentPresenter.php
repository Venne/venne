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
			$presenter->redirect('edit', array('key' => $entity->id));
		});
		$table->addAction('delete', 'Delete', function($entity) use ($presenter)
		{
			$presenter->redirect('delete!', array('id' => $entity->id));
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
		$form->onSuccess[] = function($form) use ($repository)
		{
			if ($repository->isUnique($form->entity)) {
				$repository->save($form->entity);

				$form->getPresenter()->flashMessage("Page has been created");
				$form->getPresenter()->redirect("edit", array("type" => null, 'key' => $form->entity->id));
			} else {
				$form->presenter->flashMessage("URL is not unique", "warning");
			}
		};
		return $form;
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
		$form->onSuccess[] = function($form) use ($repository)
		{
			if ($repository->isUnique($form->entity)) {
				$repository->save($form->entity);

				$form->getPresenter()->flashMessage("Page has been created");
				$form->getPresenter()->redirect("edit", array("type" => null, 'key' => $form->entity->id));
			} else {
				$form->presenter->flashMessage("URL is not unique", "warning");
			}
		};
		return $form;
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
			$form->onSuccess[] = function($form) use ($repository)
			{
				if ($repository->isUnique($form->entity)) {
					$repository->save($form->entity);

					$form->getPresenter()->flashMessage("Page has been updated");
					$form->getPresenter()->redirect("this");
				} else {
					$form->presenter->flashMessage("URL is not unique", "warning");
				}
			};
		}
		return $form;
	}


	public function handleDelete($id)
	{
		$this->pageRepository->delete($this->pageRepository->find($id));
		$this->flashMessage("Page has been deleted", "success");
		$this->redirect("this");
	}


	public function renderEdit()
	{
		$this->template->entity = $this->pageRepository->find($this->getParameter("key"));
		$this->template->contentType = $this->contentManager->getContentType($this->template->entity->type);
		$sections = $this->template->contentType->getSections();
		$this->template->section = $this->section ? : reset($sections)->name;
	}
}
