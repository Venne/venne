<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace CmsModule\Content\Presenters;

use CmsModule\Administration\Presenters\ContentPresenter;
use CmsModule\Content\ElementManager;
use CmsModule\Content\Elements\BaseElement;
use CmsModule\Content\Entities\ExtendedPageEntity;
use CmsModule\Content\Entities\ExtendedRouteEntity;
use CmsModule\Content\Entities\LanguageEntity;
use CmsModule\Content\Entities\PageEntity;
use CmsModule\Content\Entities\RouteEntity;
use CmsModule\Content\Repositories\LanguageRepository;
use CmsModule\Content\Repositories\PageRepository;
use CmsModule\Content\Repositories\RouteRepository;
use Nette\Application\BadRequestException;
use Nette\Application\ForbiddenRequestException;
use Nette\Application;
use Nette\Application\Responses;
use Nette\ComponentModel\IComponent;
use Nette\Utils\Strings;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 *
 * @property-read ElementManager $elementManager
 * @property-read RouteRepository $routeRepository
 * @property-read PageRepository $pageRepository
 * @property-read LanguageRepository $languageRepository
 * @property-read PageEntity $page
 * @property-read RouteEntity $route
 * @property-read ExtendedPageEntity $extendedPage
 * @property-read ExtendedRouteEntity $extendedRoute
 */
class PagePresenter extends \CmsModule\Presenters\FrontPresenter
{

	/**
	 * @persistent
	 * @var int
	 */
	public $routeId;

	/**
	 * @persistent
	 * @var RouteEntity
	 */
	public $_route;

	/** @var LanguageEntity */
	private $language;

	/** @var string */
	protected $_layoutFile;

	/** @var \Venne\Module\Helpers */
	protected $moduleHelpers;

	/** @var RouteRepository */
	private $routeRepository;

	/** @var PageRepository */
	private $pageRepository;

	/** @var LanguageRepository */
	private $languageRepository;

	/** @var ElementManager */
	private $elementManager;


	/**
	 * @param \Venne\Module\Helpers $moduleHelpers
	 * @param PageRepository $pageRepository
	 * @param LanguageRepository $languageRepository
	 * @param RouteRepository $routeRepository
	 * @param ElementManager $elementManager
	 */
	public function injectDefaultsInPagePresenter(
		\Venne\Module\Helpers $moduleHelpers,
		PageRepository $pageRepository,
		LanguageRepository $languageRepository,
		RouteRepository $routeRepository,
		ElementManager $elementManager
	)
	{
		$this->moduleHelpers = $moduleHelpers;
		$this->pageRepository = $pageRepository;
		$this->languageRepository = $languageRepository;
		$this->routeRepository = $routeRepository;
		$this->elementManager = $elementManager;
	}


	/**
	 * @return PageRepository
	 */
	public function getPageRepository()
	{
		return $this->pageRepository;
	}


	/**
	 * @return LanguageRepository
	 */
	public function getLanguageRepository()
	{
		return $this->languageRepository;
	}


	/**
	 * @return RouteRepository
	 */
	public function getRouteRepository()
	{
		return $this->routeRepository;
	}


	/**
	 * @return ElementManager
	 */
	public function getElementManager()
	{
		return $this->elementManager;
	}


	/**
	 * @throws \Nette\Application\ForbiddenRequestException
	 * @throws \Nette\Application\BadRequestException
	 */
	protected function startup()
	{
		if (!$this->routeId) {
			throw new BadRequestException;
		}

		parent::startup();
	}


	/**
	 * @return RouteEntity
	 * @throws \Nette\Application\BadRequestException
	 */
	public function getRoute()
	{
		if (!$this->_route) {
			if (($this->_route = $this->routeRepository->find($this->routeId)) === NULL) {
				throw new BadRequestException;
			}

			// preview
			if (!$this->_route->page->published || !$this->_route->published || $this->_route->released > new \DateTime) {
				$session = $this->getSession(ContentPresenter::PREVIEW_SESSION);

				if (!isset($session->routes[$this->_route->route->id])) {
					throw new BadRequestException;
				} else {
					$this->flashMessage($this->translator->translate('This page is unpublished.'), 'info');
				}
			}

			if ($this->getPage()->secured && !$this->user->isLoggedIn()) {
				$this->redirect('Route', array('special' => 'login', 'backlink' => $this->storeRequest()));
			}

			if (!$this->isAllowed('show')) {
				throw new ForbiddenRequestException;
			}
		}
		return $this->_route;
	}


	/**
	 * @return PageEntity
	 */
	public function getPage()
	{
		return $this->getRoute()->page;
	}


	/**
	 * @return ExtendedPageEntity
	 */
	public function getExtendedPage()
	{
		return $this->getRoute()->page->extendedPage;
	}


	/**
	 * @return ExtendedRouteEntity
	 */
	public function getExtendedRoute()
	{
		return $this->getRoute()->extendedRoute;
	}


	/**
	 * @return \CmsModule\Content\Entities\LanguageEntity
	 */
	public function getLanguage()
	{
		if (!$this->language) {
			$this->language = $this->languageRepository->findOneBy(array('alias' => $this->lang ? $this->lang : $this->websiteManager->defaultLanguage));
		}
		return $this->language;
	}


	/**
	 * Redirect to other language.
	 *
	 * @param string $alias
	 */
	public function handleChangeLanguage($alias)
	{
		if (!$this->getPage()->getLanguage()) {
			$this->getRoute()->locale = $this->languageRepository->findOneBy(array('alias' => $alias));
			$this->redirect('this', array('route' => $this->getRoute(), 'lang' => $alias));
		}

		$page = $this->getPage()->parent;
		do {
			$page->mainRoute->locale = $alias;
			$this->entityManager->refresh($page->mainRoute);
			if (!$page->getLanguage() || $page->getLanguage() == $alias) {
				$this->redirect('this', array('route' => $page->mainRoute, 'lang' => $alias));
			}
			$page = $page->parent;
		} while ($page);
		throw new BadRequestException;
	}


	/**
	 * Get layout path.
	 *
	 * @return null|string
	 */
	public function getLayoutFile()
	{
		$layout = NULL;

		if (!$this->getRoute()->layout) {
			return $layout;
		}

		if ($this->websiteManager->theme) {
			$extendedLayout = explode('/', $this->getRoute()->getLayout()->getFile(), 2);
			$extendedLayout = '@' . $this->websiteManager->theme . 'Module/' . $extendedLayout[1];

			$layout = $this->moduleHelpers->expandPath($extendedLayout, 'Resources/layouts');
		}

		if ($layout == NULL || !file_exists($layout)) {
			$layout = $this->moduleHelpers->expandPath($this->getRoute()->getLayout()->getFile(), 'Resources/layouts');
		}

		return $layout;
	}


	/**
	 * Formats layout template file names.
	 *
	 * @return array
	 */
	public function formatLayoutTemplateFiles()
	{
		return array($this->getLayoutFile());
	}


	/**
	 * Formats view template file names.
	 *
	 * @return array
	 */
	public function formatTemplateFiles()
	{
		$ret = array();

		$template = explode('\\', $this->reflection->name);
		array_shift($template);
		array_shift($template);
		$template = Strings::lower(substr('@' . implode('.', $template), 0, -9));

		if ($this->action !== 'default') {
			$template .= '.' . $this->action;
		}

		$layoutPath = dirname($this->getLayoutFile());
		$globalLayoutPath = dirname(dirname(dirname(dirname($this->reflection->getFileName())))) . '/Resources/layouts';

		if ($layoutPath) {
			$ret = array(
				"$layoutPath/$template.latte",
				dirname($layoutPath) . "/$template.latte",
			);
		}

		$ret = array_merge($ret, array(
			"$globalLayoutPath/$template.latte",
		));

		return $ret;
	}


	/**
	 * @param null $resource
	 * @param null $privilege
	 * @return bool
	 */
	public function isAllowed($resource = NULL, $privilege = NULL)
	{
		return $this->getExtendedPage()->isAllowed($this->getUser(), $resource);
	}


	/**
	 * @param $name
	 * @return BaseElement|IComponent
	 */
	protected function createComponent($name)
	{
		if (strpos($name, \CmsModule\Content\ElementManager::ELEMENT_PREFIX) === 0) {
			$name = substr($name, strlen(\CmsModule\Content\ElementManager::ELEMENT_PREFIX));
			$name = explode('_', $name);
			$id = end($name);
			unset($name[count($name) - 1]);
			$name = implode('_', $name);
			$component = $this->elementManager->createInstance($id);
			$component->setRoute($this->getRoute());
			$component->setName($name);
			$component->setLanguage($this->getLanguage());
			return $component;
		}

		return parent::createComponent($name);
	}


	/**
	 * Restores current request to session.
	 * @param  string key
	 * @return void
	 */
	public function restoreRequest($key)
	{
		$session = $this->getSession('Nette.Application/requests');
		if (!isset($session[$key]) || ($session[$key][0] !== NULL && $session[$key][0] !== $this->getUser()->getId())) {
			return;
		}
		$request = clone $session[$key][1];
		unset($session[$key]);
		$request->setFlag(Application\Request::RESTORED, TRUE);
		$params = $request->getParameters();
		$params[self::FLASH_KEY] = $this->getParameter(self::FLASH_KEY);
		if (isset($params['route'])) {
			$params['route'] = $this->getEntityManager()->getRepository(get_class($params['route']))->find($params['route']->id);
		}
		$request->setParameters($params);
		$this->sendResponse(new Responses\ForwardResponse($request));
	}


	protected function createComponentHead()
	{
		$head = $this->getWidgetManager()->getWidget('head')->invoke();
		$head->setTitle($this->getRoute()->title);
		$head->setRobots($this->getRoute()->robots);
		$head->setKeywords($this->getRoute()->keywords);
		$head->setDescription($this->getRoute()->description);
		$head->setAuthor($this->getRoute()->author);
		return $head;
	}
}
