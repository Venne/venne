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
use CmsModule\Content\Components\ContentTableFactory;
use CmsModule\Content\Components\RouteControl;
use CmsModule\Content\Entities\LanguageEntity;
use CmsModule\Content\Entities\PageEntity;
use CmsModule\Content\Repositories\LanguageRepository;
use Gedmo\Translatable\TranslatableListener;
use CmsModule\Content\Repositories\PageRepository;
use DoctrineModule\Repositories\BaseRepository;
use CmsModule\Content\ContentManager;
use CmsModule\Content\Forms\BasicFormFactory;
use Grido\DataSources\Doctrine;
use Nette\Application\BadRequestException;

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
	public $contentLang;

	/** @persistent */
	public $section;

	/** @var LanguageEntity */
	public $languageEntity;

	/** @var PageRepository */
	protected $pageRepository;

	/** @var LanguageRepository */
	protected $languageRepository;

	/** @var ContentManager */
	protected $contentManager;

	/** @var BasicFormFactory */
	protected $contentFormFactory;

	/** @var RouteControl */
	protected $contentRouteControlFactory;

	/** @var ContentTableFactory */
	protected $contentTableFactory;


	public function __construct(PageRepository $pageRepository, LanguageRepository $languageRepository, ContentManager $contentManager, ContentTableFactory $contentTableFactory, $routeControlFactory)
	{
		parent::__construct();

		$this->pageRepository = $pageRepository;
		$this->languageRepository = $languageRepository;
		$this->contentManager = $contentManager;
		$this->contentTableFactory = $contentTableFactory;
		$this->contentRouteControlFactory = $routeControlFactory;
	}


	/**
	 * @param BasicFormFactory $contentForm
	 */
	public function injectContentForm(BasicFormFactory $contentForm)
	{
		$this->contentFormFactory = $contentForm;
	}


	public function startup()
	{
		parent::startup();

		if ($this->contentLang) {
			foreach ($this->context->entityManager->getEventManager()->getListeners() as $event => $listeners) {
				foreach ($listeners as $hash => $listener) {
					if ($listener instanceof TranslatableListener) {
						$listener->setTranslatableLocale($this->contentLang);
						$listener->setTranslationFallback(TRUE);
						$break = TRUE;
						break;
					}
				}
				if (isset($break)) {
					break;
				}
			}
			$this->languageEntity = $this->languageRepository->find($this->contentLang);
		} else {
			$this->languageEntity = $this->languageRepository->findOneBy(array('short' => $this->context->parameters['website']['defaultLanguage']));
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
	 * @secured(privilege="edit")
	 */
	public function handlePublish()
	{
		$this->handleOn($this->key);
	}


	/**
	 * @secured(privilege="edit")
	 */
	public function handleHide()
	{
		$this->handleOff($this->key);
	}


	/**
	 * @secured(privilege="edit")
	 */
	public function handleRemove($id)
	{
		if (!$entity = $this->pageRepository->find($id)) {
			throw new BadRequestException;
		}

		$this->pageRepository->delete($entity);
		$this->flashMessage("Page `$entity` has been removed");

		if (!$this->isAjax()) {
			$this->redirect('this');
		}

		$this->invalidateControl('content');
		$this->payload->url = $this->link('this');
	}


	/**
	 * @secured(privilege="edit")
	 */
	public function handleOn($id)
	{
		if (!$entity = $this->pageRepository->find($id)) {
			throw new BadRequestException;
		}

		$entity->published = TRUE;
		$this->pageRepository->save($entity);

		if (!$this->isAjax()) {
			$this->redirect('this');
		}

		$this->invalidateControl('content');
		$this->payload->url = $this->link('this');
	}


	/**
	 * @secured(privilege="edit")
	 */
	public function handleOff($id)
	{
		if (!$entity = $this->pageRepository->find($id)) {
			throw new BadRequestException;
		}

		$entity->published = FALSE;
		$this->pageRepository->save($entity);

		if (!$this->isAjax()) {
			$this->redirect('this');
		}

		$this->invalidateControl('content');
		$this->payload->url = $this->link('this');
	}


	/**
	 * @secured(privilege="edit")
	 */
	public function handleSetAsRoot($id)
	{
		if (!$entity = $this->pageRepository->find($id)) {
			throw new BadRequestException;
		}

		$main = $entity->getRoot();
		$entity->setAsRoot();
		$this->pageRepository->save($main);

		if (!$this->isAjax()) {
			$this->redirect('this');
		}

		$this['panel']->invalidateControl('content');
		$this->invalidateControl('content');
		$this->payload->url = $this->link('this');
	}


	/**
	 * @secured(privilege="edit")
	 */
	public function handleDelete($id)
	{
		if (!$entity = $this->pageRepository->find($id)) {
			throw new BadRequestException;
		}

		$link = $this->link('this', array('key' => $entity->translationFor->id));

		$this->pageRepository->delete($entity);
		$this->flashMessage('Translation has been removed', 'success');
		$this->redirectUrl($link);
	}


	public function createComponentTable()
	{
		$adminGrid = new AdminGrid($this->pageRepository);

		$_this = $this;
		$table = $adminGrid->getTable();

		$table->addColumn('name', 'Name')
			->setSortable()
			->setFilter()->setSuggestion();
		$table->getColumn('name')->getCellPrototype()->width = '50%';
		$table->addColumn('mainRoute', 'URL')
			->setSortable()
			->setFilter()->setSuggestion();
		$table->getColumn('mainRoute')->getCellPrototype()->width = '25%';
		$table->addColumn('languages', 'Languages')
			->setCustomRender(function ($entity) {
				$ret = implode(", ", $entity->languages->toArray());
				foreach ($entity->translations as $translation) {
					$ret .= ', ' . implode(", ", $translation->languages->toArray());
				}
				return $ret;
			})
			->getCellPrototype()->width = '25%';

		$table->addAction('on', 'On')
			->setCustomRender(function ($entity, $element) {
				if ((bool)$entity->published) {
					$element->class[] = 'disabled';
				};
				return $element;
			})
			->setCustomHref(function ($entity) use ($_this) {
				return $_this->link('on!', array($entity->id));
			})
			->getElementPrototype()->class[] = 'ajax';
		$table->addAction('off', 'Off')
			->setCustomRender(function ($entity, $element) {
				if (!(bool)$entity->published) {
					$element->class[] = 'disabled';
				};
				return $element;
			})
			->setCustomHref(function ($entity) use ($_this) {
				return $_this->link('off!', array($entity->id));
			})
			->getElementPrototype()->class[] = 'ajax';
		$table->addAction('edit', 'Edit')
			->setCustomHref(function ($entity) use ($_this) {
				return $_this->link('edit', array('key' => $entity->id));
			})
			->getElementPrototype()->class[] = 'ajax';
		$table->addAction('setAsRoot', 'Set as root')
			->setCustomRender(function ($entity, $element) {
				if ($entity->parent === NULL || $entity->tag) {
					$element->class[] = 'disabled';
				};
				return $element;
			})
			->setCustomHref(function ($entity) use ($_this) {
				return $_this->link('setAsRoot!', array($entity->id));
			})
			->getElementPrototype()->class[] = 'ajax';
		$table->addAction('remove', 'Remove')
			->getElementPrototype()->class[] = 'ajax';

		$adminGrid->connectActionAsDelete($table->getAction('remove'));

		return $adminGrid;
	}


	public function createComponentTable2()
	{
		$presenter = $this;

		$table = $this->contentTableFactory->create();

		// actions
		$repository = $this->pageRepository;
		if ($this->isAuthorized('edit')) {
			$action = $table->addAction('on', 'On');
			$action->onClick[] = function ($button, $entity) use ($presenter, $repository) {
				$entity->published = TRUE;
				$repository->save($entity);

				if (!$presenter->isAjax()) {
					$presenter->redirect('this');
				}

				$presenter->invalidateControl('content');
				$presenter['panel']->invalidateControl('content');
				$presenter->payload->url = $presenter->link('this');
			};
			$action->onRender[] = function ($button, $entity) use ($presenter, $repository) {
				$button->setDisabled($entity->published);
			};

			$action = $table->addAction('off', 'Off');
			$action->onClick[] = function ($button, $entity) use ($presenter, $repository) {
				$entity->published = FALSE;
				$repository->save($entity);

				if (!$presenter->isAjax()) {
					$presenter->redirect('this');
				}

				$presenter->invalidateControl('content');
				$presenter['panel']->invalidateControl('content');
				$presenter->payload->url = $presenter->link('this');
			};
			$action->onRender[] = function ($button, $entity) use ($presenter, $repository) {
				$button->setDisabled(!$entity->published);
			};

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
			$action = $table->addAction('setAsRoot', 'Set as root');
			$action->onRender[] = function ($button, $entity) {
				$button->setDisabled($entity->parent === NULL || $entity->tag);
			};
			$action->onClick[] = function ($button, $entity) use ($presenter, $repository) {
				$main = $entity->getRoot();
				$entity->setAsRoot();
				$repository->save($main);

				if (!$presenter->isAjax()) {
					$presenter->redirect('this');
				}
				$presenter->invalidateControl('content');
				$presenter['panel']->invalidateControl('content');
				$presenter->payload->url = $presenter->link('this');
			};
		}

		if ($this->isAuthorized('remove')) {
			$table->addActionDelete('delete', 'Delete')->onSuccess[] = function () use ($presenter) {
				$presenter['panel']->invalidateControl('content');
			};

			// global actions
			$table->setGlobalAction($table['delete']);
		}

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
			$this->redirect('edit', array('type' => NULL, 'key' => $form->data->id));
		}
		$this->invalidateControl('content');
		$this->payload->url = $this->link('edit', array('type' => NULL, 'key' => $form->data->id));
		$this->setView('edit');
		$this->changeAction('edit');
		$this->key = $form->data->id;
	}


	public function createComponentFormTranslate()
	{
		$pageEntity = $this->pageRepository->find($this->getParameter("key"));
		$contentType = $this->contentManager->getContentType(get_class($pageEntity));

		/** @var $entity \CmsModule\Content\Entities\PageEntity */
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
		$contentType = $this->contentManager->getContentType(get_class($entity));

		if ((!$this->section && count($contentType->sections) == 0) || $this->section == 'basic') {
			$form = $this->contentFormFactory->invoke($entity);
		} elseif ($this->section == 'routes') {
			$form = $this->contentRouteControlFactory->invoke();
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
		$this->template->contentType = $this->contentManager->getContentType(get_class($this->template->entity));
		$sections = $this->template->contentType->getSections();
		$this->template->section = $this->section ? : reset($sections)->name;
		$this->template->languageRepository = $this->languageRepository;
	}


	public function renderTranslate()
	{
		$this->template->entity = $this->pageRepository->find($this->key);
	}
}
