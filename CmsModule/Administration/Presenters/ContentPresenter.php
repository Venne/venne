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

use CmsModule\Administration\Forms\PublishFormFactory;
use CmsModule\Content\Components\ContentTableFactory;
use CmsModule\Content\Components\RouteControl;
use CmsModule\Content\ContentManager;
use CmsModule\Content\Entities\ExtendedPageEntity;
use CmsModule\Content\Entities\LanguageEntity;
use CmsModule\Content\Forms\AdminPermissionsFormFactory;
use CmsModule\Content\Forms\BasicFormFactory;
use CmsModule\Content\Forms\PermissionsFormFactory;
use CmsModule\Content\ISectionControl;
use CmsModule\Content\Repositories\LanguageRepository;
use CmsModule\Content\Repositories\PageRepository;
use CmsModule\Pages\Users\UserEntity;
use DoctrineModule\Repositories\BaseRepository;
use Nette\Application\BadRequestException;
use Nette\Application\ForbiddenRequestException;
use Nette\Application\UI\InvalidLinkException;
use Nette\InvalidArgumentException;
use Venne\Forms\Form;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 *
 * @secured
 */
class ContentPresenter extends BasePresenter
{

	const PREVIEW_SESSION = 'venne.content.preview';

	/** @persistent */
	public $key;

	/** @persistent */
	public $type;

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

	/** @var PermissionsFormFactory */
	protected $permissionsFormFactory;

	/** @var AdminPermissionsFormFactory */
	protected $adminPermissionsFormFactory;

	/** @var PublishFormFactory */
	protected $publishFormFactory;

	/** @var ExtendedPageEntity */
	private $_pageEntity;


	public function __construct(PageRepository $pageRepository, LanguageRepository $languageRepository, ContentManager $contentManager, ContentTableFactory $contentTableFactory, $routeControlFactory, PermissionsFormFactory $permissionsFormFactory, AdminPermissionsFormFactory $adminPermissionsFormFactory, PublishFormFactory $publishFormFactory)
	{
		parent::__construct();

		$this->pageRepository = $pageRepository;
		$this->languageRepository = $languageRepository;
		$this->contentManager = $contentManager;
		$this->contentTableFactory = $contentTableFactory;
		$this->contentRouteControlFactory = $routeControlFactory;
		$this->permissionsFormFactory = $permissionsFormFactory;
		$this->adminPermissionsFormFactory = $adminPermissionsFormFactory;
		$this->publishFormFactory = $publishFormFactory;
	}


	/**
	 * @param BasicFormFactory $contentForm
	 */
	public function injectContentForm(BasicFormFactory $contentForm)
	{
		$this->contentFormFactory = $contentForm;
	}


	protected function startup()
	{
		parent::startup();

		if ($this->contentLang) {
			$this->languageEntity = $this->languageRepository->findOneBy(array('alias' => $this->contentLang));
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
		if (!$this->isAllowedInBackend(ExtendedPageEntity::ADMIN_PRIVILEGE_SHOW)) {
			throw new ForbiddenRequestException;
		}

		$entity = $this->getPageEntity();
		$page = $entity->getPage();
		if (!$page->getPublished() || !$page->getMainRoute()->getPublished()) {
			$this->flashMessage($this->translator->translate('This page is not published.'), 'warning', TRUE);
		} else if ($page->getMainRoute()->getReleased() && $page->getMainRoute()->getReleased() > new \DateTime()) {
			$this->flashMessage($this->translator->translate('Release date is already scheduled.'), 'info', TRUE);
		} else if ($page->getMainRoute()->getExpired() && $page->getMainRoute()->getExpired() > new \DateTime()) {
			$this->flashMessage($this->translator->translate('Expire date is already scheduled.'), 'info', TRUE);
		} else if ($page->getMainRoute()->getExpired() && $page->getMainRoute()->getExpired() < new \DateTime()) {
			$this->flashMessage($this->translator->translate('This page expired.'), 'warning', TRUE);
		}

		$contentType = $this->getContentType();
		if (!$this->section || (!in_array($this->section, array('basic', 'routes', 'permissions', 'admin_permissions')) && !$contentType->hasSection($this->section))) {
			$sections = $contentType->getSections();

			if (count($sections)) {
				$this->section = reset($sections)->name;
			} else {
				$this->section = 'basic';
			}
		}

		if ($this->section === 'basic') {
			if (!$this->isAllowedInBackend(ExtendedPageEntity::ADMIN_PRIVILEGE_BASE)) {
				throw new ForbiddenRequestException;
			}
		} elseif ($this->section === 'routes') {
			if (!$this->isAllowedInBackend(ExtendedPageEntity::ADMIN_PRIVILEGE_ROUTES)) {
				throw new ForbiddenRequestException;
			}
		} elseif ($this->section === 'permissions' || $this->section === 'admin_permissions') {
			if (!$this->isAllowedInBackend(ExtendedPageEntity::ADMIN_PRIVILEGE_PERMISSIONS)) {
				throw new ForbiddenRequestException;
			}
		}
	}


	public function isAllowedInBackend($permission)
	{
		return $this->getPageEntity()->isAllowedInBackend($this->user, $permission);
	}


	/**
	 * @secured(privilege="edit")
	 */
	public function handlePreview($id)
	{
		if ($id) {
			if (!$entity = $this->pageRepository->find($id)) {
				throw new BadRequestException;
			}
			$route = $entity->mainRoute;
		} else {
			$route = $this->getPageEntity()->page->getMainRoute();
			$entity = $route->getPage();
		}

		if (!$entity->isAllowedInBackend($this->user, ExtendedPageEntity::ADMIN_PRIVILEGE_PREVIEW)) {
			throw new ForbiddenRequestException;
		}

		if (!$entity->published || !$route->published || ($route->released && $route->released > new \DateTime)) {
			$session = $this->getSession(self::PREVIEW_SESSION);
			$session->setExpiration('+ 2 minutes');
			if (!isset($session->routes)) {
				$session->routes = array();
			}
			$session->routes[$route->id] = TRUE;
		}

		$this->redirect(':Cms:Pages:Text:Route:', array('route' => $route, 'key' => NULL, 'lang' => $this->contentLang));
	}


	/**
	 * @secured(privilege="edit")
	 */
	public function handleRemove($id)
	{
		if (!$entity = $this->pageRepository->find($id)) {
			throw new BadRequestException;
		}

		if (!$entity->isAllowedInBackend($this->user, ExtendedPageEntity::ADMIN_PRIVILEGE_REMOVE)) {
			throw new ForbiddenRequestException;
		}

		$this->pageRepository->delete($entity);
		$this->flashMessage($this->translator->translate('Page \'%name%\' has been removed.', NULL, array('name' => (string)$entity)), 'success');

		if (!$this->isAjax()) {
			$this->redirect('this');
		}

		$this->invalidateControl('content');
		$this->payload->url = $this->link('this');
	}


	/**
	 * @secured(privilege="edit")
	 */
	public function handlePublish($id = NULL)
	{
		$id = $id ? : $this->key;

		if (!$entity = $this->pageRepository->find($id)) {
			throw new BadRequestException;
		}

		if (!$entity->isAllowedInBackend($this->user, ExtendedPageEntity::ADMIN_PRIVILEGE_PUBLICATION)) {
			throw new ForbiddenRequestException;
		}

		$entity->published = !$entity->published;
		$entity->mainRoute->published = $entity->published;
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

		if (!$entity->isAllowedInBackend($this->user, ExtendedPageEntity::ADMIN_PRIVILEGE_CHANGE_STRUCTURE)) {
			throw new ForbiddenRequestException;
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
		$this->flashMessage($this->translator->translate('Translation has been removed.'), 'success');
		$this->redirectUrl($link);
	}


	protected function createComponentTable()
	{
		$_this = $this;
		$adminGrid = $this->contentTableFactory->create();
		$table = $adminGrid->getTable();
		$table->setTranslator($this->presenter->context->translator->translator);

		if ($this->isAuthorized('edit')) {
			$table->addAction('publish', 'published')
				->setCustomRender(function ($entity, $element) use ($_this) {
					if ((bool)$entity->published) {
						$element->class[] = 'btn-primary';
					};
					if (!$entity->isAllowedInBackend($_this->user, ExtendedPageEntity::ADMIN_PRIVILEGE_PUBLICATION)) {
						$element->class[] = 'disabled';
					};
					return $element;
				})
				->setCustomHref(function ($entity) use ($_this) {
					return $_this->link('publish!', array($entity->id));
				})
				->getElementPrototype()->class[] = 'ajax';
			$table->addAction('preview', 'Preview')
				->setCustomHref(function ($entity) use ($_this) {
					return $_this->link('preview!', array($entity->id));
				})
				->setCustomRender(function ($entity, $element) use ($_this) {
					if (!$entity->isAllowedInBackend($_this->user, ExtendedPageEntity::ADMIN_PRIVILEGE_PREVIEW)) {
						$element->class[] = 'disabled';
					};
					return $element;
				});

			$table->addAction('edit', 'Edit')
				->setCustomHref(function ($entity) use ($_this) {
					return $_this->link('edit', array('key' => $entity->id));
				})
				->setCustomRender(function ($entity, $element) use ($_this) {
					if (!$entity->isAllowedInBackend($_this->user, ExtendedPageEntity::ADMIN_PRIVILEGE_SHOW)) {
						$element->class[] = 'disabled';
					};
					return $element;
				})
				->getElementPrototype()->class[] = 'ajax';

			$table->addAction('setAsRoot', 'Set as root')
				->setCustomRender(function ($entity, $element) use ($_this) {
					if ($entity->parent === NULL) {
						$element->class[] = 'disabled';
					};
					if (!$entity->isAllowedInBackend($_this->user, ExtendedPageEntity::ADMIN_PRIVILEGE_CHANGE_STRUCTURE)) {
						$element->class[] = 'disabled';
					};
					return $element;
				})
				->setCustomHref(function ($entity) use ($_this) {
					return $_this->link('setAsRoot!', array($entity->id));
				})
				->getElementPrototype()->class[] = 'ajax';
			$table->addAction('remove', 'Remove')
				->setCustomRender(function ($entity, $element) use ($_this) {
					if (!$entity->isAllowedInBackend($_this->user, ExtendedPageEntity::ADMIN_PRIVILEGE_REMOVE)) {
						$element->class[] = 'disabled';
					};
					return $element;
				})
				->getElementPrototype()->class[] = 'ajax';

			$adminGrid->connectActionAsDelete($table->getAction('remove'));

			$table->getAction('remove')->onClick[] = function () use ($_this) {
				$_this['panel']->invalidateControl('content');
			};
		}

		return $adminGrid;
	}


	protected function createComponentForm()
	{
		$contentType = $this->contentManager->getContentType($this->getParameter('type'));
		$entity = $this->pageRepository->createNewByEntityName($contentType->getEntityName());

		if ($this->user->identity instanceof UserEntity) {
			$entity->getExtendedMainRoute()->route->author = $this->user->identity;
		}

		$form = $this->contentFormFactory->invoke($entity);
		$form->onSuccess[] = $this->formSuccess;
		$form->onError[] = $this->formError;
		return $form;
	}


	public function formSuccess($form)
	{
		$this->flashMessage($this->translator->translate('Page has been created.'), 'success');

		if (!$this->isAjax()) {
			if ($this->isAuthorized('edit')) {
				$this->redirect('edit', array('type' => NULL, 'key' => $form->data->page->id, 'section' => NULL));
			} else {
				$this->redirect('default', array('type' => NULL, 'key' => NULL));
			}
		}

		$this->invalidateControl('content');
		$this['panel']->invalidateControl('content');

		$this->section = NULL;
		$this->type = NULL;
		if ($this->isAuthorized('edit')) {
			$this->setView('edit');
			$this->changeAction('edit');
			$this->key = $form->data->page->id;
			$this->payload->url = $this->link('edit', array('type' => NULL, 'key' => $form->data->page->id, 'section' => NULL));
		} else {
			$this->setView('default');
			$this->key = NULL;
			$this->payload->url = $this->link('default', array('type' => NULL, 'key' => NULL));
		}
	}


	public function formError()
	{
		$this->invalidateControl('content');
	}


	protected function createComponentFormEdit()
	{
		$entity = $this->getPageEntity();
		$contentType = $this->getContentType();

		if ($this->section == 'basic') {
			$form = $this->contentFormFactory->invoke($entity);
		} elseif ($this->section == 'routes') {
			$form = $this->contentRouteControlFactory->invoke();
		} elseif ($this->section == 'permissions') {
			$form = $this->permissionsFormFactory->invoke($entity);
		} elseif ($this->section == 'admin_permissions') {
			$form = $this->adminPermissionsFormFactory->invoke($entity);
		} else {
			if ($this->section) {
				if (!$contentType->hasSection($this->section)) {
					throw new InvalidLinkException("Section '$this->section' not exists");
				}
				$formFactory = $contentType->sections[$this->section]->getFormFactory();
			} else {
				$sections = $contentType->sections;
				$formFactory = reset($sections)->getFormFactory();
			}
			$form = $formFactory->invoke($entity);
		}

		if ($form instanceof ISectionControl) {
			$form->setExtendedPage($entity);
		} else if ($form instanceof Form) {
			$form->onSuccess[] = $this->formEditSuccess;
			$form->onError[] = $this->formError;
		} else {
			throw new InvalidArgumentException("Control must be instance of 'Venne\Forms\Form' OR 'CmsModule\Content\ISectionControl'. " . get_class($form) . " is given");
		}
		return $form;
	}


	public function formEditSuccess()
	{
		$this->flashMessage($this->translator->translate('Page has been updated.'), 'success');

		if (!$this->isAjax()) {
			$this->redirect('this');
		}
		$this->invalidateControl('content');
	}


	/**
	 * @return ExtendedPageEntity
	 * @throws BadRequestException
	 */
	public function getPageEntity()
	{
		if (!$this->_pageEntity) {
			if (!$this->_pageEntity = $this->pageRepository->find($this->key)) {
				throw new BadRequestException;
			}

			if (!$this->_pageEntity = $this->context->entityManager->getRepository($this->_pageEntity->class)->findOneBy(array('page' => $this->_pageEntity->id))) {
				throw new BadRequestException;
			}

			if ($this->context->parameters['website']['defaultLanguage'] !== $this->languageEntity->alias) {
				$this->_pageEntity->page->mainRoute->locale = $this->languageEntity;
			}
		}

		return $this->_pageEntity;
	}


	/**
	 * @return \CmsModule\Content\IContentType
	 */
	private function getContentType()
	{
		$entityClass = $this->entityManager->getClassMetadata(get_class($this->getPageEntity()))->getName();
		return $this->contentManager->getContentType($entityClass);
	}


	public function renderEdit()
	{
		$this->template->entity = $this->getPageEntity();
		$this->template->contentType = $this->getContentType();
		$sections = $this->template->contentType->getSections();
		$this->template->section = $this->section ? : reset($sections)->name;
		$this->template->languageRepository = $this->languageRepository;
	}


	protected function createComponentPublishForm()
	{
		$form = $this->publishFormFactory->invoke($this->getPageEntity());
		$form->onSuccess[] = $this->formEditSuccess;
		return $form;
	}
}
