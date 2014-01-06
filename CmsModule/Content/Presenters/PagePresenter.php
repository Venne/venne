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
use Nette\Caching\Cache;
use Nette\Caching\IStorage;
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
	 * @var int
	 */
	public $pageId;

	/**
	 * @persistent
	 * @var int
	 */
	public $slug;

	/**
	 * @persistent
	 * @var RouteEntity
	 */
	public $_route;

	/**
	 * @persistent
	 * @var PageEntity
	 */
	public $_page;

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

	/** @var IStorage */
	private $cacheStorage;

	/** @var Cache */
	private $_cache;


	/**
	 * @param \Venne\Module\Helpers $moduleHelpers
	 * @param PageRepository $pageRepository
	 * @param LanguageRepository $languageRepository
	 * @param RouteRepository $routeRepository
	 * @param ElementManager $elementManager
	 * @param IStorage $cacheStorage
	 */
	public function injectDefaultsInPagePresenter(
		\Venne\Module\Helpers $moduleHelpers,
		PageRepository $pageRepository,
		LanguageRepository $languageRepository,
		RouteRepository $routeRepository,
		ElementManager $elementManager,
		IStorage $cacheStorage
	)
	{
		$this->moduleHelpers = $moduleHelpers;
		$this->pageRepository = $pageRepository;
		$this->languageRepository = $languageRepository;
		$this->routeRepository = $routeRepository;
		$this->elementManager = $elementManager;
		$this->cacheStorage = $cacheStorage;
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

		$this->checkRoute();

		parent::startup();
	}


	protected function checkRoute()
	{
		$data = $this->loadRouteCache();
		if (!isset($data['checks'])) {
			$data['checks'] = array();
			$route = $this->getRoute();

			// preview
			if (!$route->page->published || !$route->published || $route->released > new \DateTime) {
				$data['checks']['published'] = FALSE;
			} else {
				$data['checks']['published'] = TRUE;
			}

			$data['checks']['secured'] = $route->page->secured;
			$this->saveRouteCache($data);
		}

		if (!$data['checks']['published']) {
			$session = $this->getSession(ContentPresenter::PREVIEW_SESSION);
			if (!isset($session->routes[$this->routeId])) {
				throw new BadRequestException;
			} else {
				$this->flashMessage($this->translator->translate('This page is unpublished.'), 'info');
			}
		}

		if ($data['checks']['secured'] && !$this->user->isLoggedIn()) {
			$this->redirect('Route', array('special' => 'login', 'backlink' => $this->storeRequest()));
		}

		if (!$this->isAllowed('show')) {
			throw new ForbiddenRequestException;
		}
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
		}
		return $this->_route;
	}


	/**
	 * @return PageEntity
	 */
	public function getPage()
	{
		if (!$this->_page) {
			$this->_page = $this->getRoute()->getPage();
		}

		return $this->_page;
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
			$this->getPage()->locale = $this->getRoute()->locale = $this->languageRepository->findOneBy(array('alias' => $alias));
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
		$data = $this->loadRouteCache();

		if (!array_key_exists('layout', $data)) {
			$layout = NULL;

			if (!$this->getRoute()->layout) {
				$data['layout'] = $layout;

			} else {
				if ($this->websiteManager->theme) {
					$extendedLayout = explode('/', $this->getRoute()->getLayout()->getFile(), 2);
					$extendedLayout = '@' . $this->websiteManager->theme . 'Module/' . $extendedLayout[1];

					$layout = $this->moduleHelpers->expandPath($extendedLayout, 'Resources/layouts');
				}

				if ($layout == NULL || !file_exists($layout)) {
					$layout = $this->moduleHelpers->expandPath($this->getRoute()->getLayout()->getFile(), 'Resources/layouts');
				}

				$data['layout'] = $layout;
			}

			$this->saveRouteCache($data);
		}

		return $data['layout'];
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
		$data = $this->loadRouteCache();
		$user = $this->user->isLoggedIn() ? serialize($this->user->roles)  : 0;

		if (!isset($data['isAllowed'])) {
			$data['isAllowed'] = array();
		}

		if (!isset($data['isAllowed'][$user])) {
			$data['isAllowed'][$user] = array();
		}

		if (!isset($data['isAllowed'][$user][$resource])) {
			$data['isAllowed'][$user][$resource] = $this->getExtendedPage()->isAllowed($this->getUser(), $resource);
			$this->saveRouteCache($data);
		}

		return $data['isAllowed'];
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


	/**
	 * @return Cache
	 */
	protected function getCache()
	{
		if (!$this->_cache) {
			$this->_cache = new Cache($this->cacheStorage, 'Venne.Presenter');
		}

		return $this->_cache;
	}


	/**
	 * @return array
	 */
	protected function loadRouteCache()
	{
		$data = $this->getCache()->load($this->routeId);
		if (!$data) {
			$data = array();
		}
		return $data;
	}


	/**
	 * @param $data
	 */
	protected function saveRouteCache($data)
	{
		$this->getCache()->save($this->routeId, $data, array(
			Cache::TAGS => array(
				'route' => $this->routeId,
				'page' => $this->pageId,
			),
		));
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
