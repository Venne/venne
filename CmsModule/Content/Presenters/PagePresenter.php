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
use Nette\InvalidArgumentException;
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

	const CACHE_OUTPUT = 'Venne.Output';

	/**
	 * @persistent
	 * @var RouteEntity
	 */
	public $route;

	/** @var ExtendedRouteEntity */
	private $extendedRoute;

	/** @var PageEntity */
	private $page;

	/** @var ExtendedPageEntity */
	private $extendedPage;

	/** @var LanguageEntity */
	private $language;

	/** @var string */
	protected $_layoutFile;

	/** @var \Nette\Caching\Cache */
	protected $_templateCache;

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


	public function injectTemplateCache(\Nette\Caching\IStorage $cache)
	{
		$this->_templateCache = new Cache($cache, self::CACHE_OUTPUT);
	}


	/**
	 * @param \Venne\Module\Helpers $moduleHelpers
	 */
	public function injectModulesHelper(\Venne\Module\Helpers $moduleHelpers)
	{
		$this->moduleHelpers = $moduleHelpers;
	}


	/**
	 * @param PageRepository $pageRepository
	 */
	public function injectPageRepository(PageRepository $pageRepository)
	{
		$this->pageRepository = $pageRepository;
	}


	/**
	 * @param LanguageRepository $languageRepository
	 */
	public function injectLanguageRepository(LanguageRepository $languageRepository)
	{
		$this->languageRepository = $languageRepository;
	}


	/**
	 * @param RouteRepository $routeRepository
	 */
	public function injectRouteRepository(RouteRepository $routeRepository)
	{
		$this->routeRepository = $routeRepository;
	}


	/**
	 * @param ElementManager $elementManager
	 */
	public function injectElementManager(ElementManager $elementManager)
	{
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
	 * @return void
	 */
	protected function startup()
	{
		if (!$this->route) {
			throw new BadRequestException;
		}

		if (!$this->route instanceof ExtendedRouteEntity) {
			throw new InvalidArgumentException("Route must be instance of 'CmsModule\Content\Entities\ExtendedRouteEntity'. '" . get_class($this->route) . "' is given.");
		}

		// preview
		if (!$this->route->page->published || !$this->route->route->published || $this->route->route->released > new \DateTime) {
			$session = $this->getSession(ContentPresenter::PREVIEW_SESSION);

			if (!isset($session->routes[$this->route->route->id])) {
				throw new BadRequestException;
			} else {
				$this->flashMessage($this->translator->translate('This page is unpublished.'), 'info');
			}
		}

		$this->extendedRoute = $this->route;
		$this->route = $this->route->route;

		if ($this->getPage()->secured && !$this->user->isLoggedIn()) {
			$this->redirect('Route', array('special' => 'login', 'backlink' => $this->storeRequest()));
		}

		if (!$this->isAllowed('show')) {
			throw new ForbiddenRequestException;
		}

		parent::startup();
	}


	/**
	 * @return PageEntity
	 */
	public function getPage()
	{
		if (!$this->page) {
			$this->page = $this->route->page;
		}
		return $this->page;
	}


	/**
	 * @return ExtendedPageEntity
	 */
	public function getExtendedPage()
	{
		if (!$this->extendedPage) {
			$this->extendedPage = $this->extendedRoute->extendedPage;
		}
		return $this->extendedPage;
	}


	/**
	 * @return ExtendedRouteEntity
	 */
	public function getExtendedRoute()
	{
		return $this->extendedRoute;
	}


	/**
	 * @return LanguageEntity
	 */
	public function getLanguage()
	{
		if (!$this->language) {
			$this->language = $this->languageRepository->findOneBy(array('alias' => $this->lang ? $this->lang : $this->context->parameters['website']['defaultLanguage']));
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
			$this->route->locale = $this->languageRepository->findOneBy(array('alias' => $alias));
			$this->redirect('this', array('route' => $this->route, 'lang' => $alias));
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
		if ($this->_layoutFile === NULL) {
			if (!$this->route->layout) {
				$this->_layoutFile = FALSE;
			} else {
				if (isset($this->context->parameters['website']['theme']) && $this->context->parameters['website']['theme']) {
					$extendedLayout = explode('/', $this->route->getLayout()->getFile(), 2);
					$extendedLayout = '@' . $this->context->parameters['website']['theme'] . 'Module/' . $extendedLayout[1];

					$this->_layoutFile = $this->moduleHelpers->expandPath($extendedLayout, 'Resources/layouts');
				}

				if ($this->_layoutFile == NULL || !file_exists($this->_layoutFile)) {
					$this->_layoutFile = $this->moduleHelpers->expandPath($this->route->getLayout()->getFile(), 'Resources/layouts');
				}
			}
		}

		return $this->_layoutFile === FALSE ? NULL : $this->_layoutFile;
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


	protected function createComponent($name)
	{
		if (strpos($name, \CmsModule\Content\ElementManager::ELEMENT_PREFIX) === 0) {
			$name = substr($name, strlen(\CmsModule\Content\ElementManager::ELEMENT_PREFIX));
			$name = explode('_', $name);
			$id = end($name);
			unset($name[count($name) - 1]);
			$name = implode('_', $name);
			$component = $this->elementManager->createInstance($id);
			$component->setRoute($this->route);
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
	 * Common render method.
	 *
	 * @return void
	 */
	public function beforeRender()
	{
		// Cache all page
		if (!$this->isAuthorized(':Cms:Admin:Panel:') && $this->route->getCacheMode()) {
			$presenter = $this;
			$templateCache = $this->_templateCache;
			$key = $this->getHttpRequest()->getUrl()->getAbsoluteUrl() . ($this->getUser()->isLoggedIn() ? '|logged' : '');
			$this->getApplication()->onResponse[] = function () {
				ob_start();
			};
			$this->getApplication()->onShutdown[] = function () use ($presenter, $templateCache, $key) {
				$output = ob_get_clean();

				if ($presenter instanceof PagePresenter) {
					$parameters = array(
						Cache::TAGS => array(
							'route' => $presenter->route->id,
							'page' => $presenter->page->id,
						),
					);

					if ($presenter->route->getCacheMode() == RouteEntity::DEFAULT_CACHE_MODE) {
						$cacheMode = $presenter->context->parameters['website']['cacheMode'];
					} else {
						$cacheMode = $presenter->route->getCacheMode();
					}

					if ($cacheMode) {
						if ($cacheMode === RouteEntity::CACHE_MODE_TIME) {
							$parameters[Cache::EXPIRE] = '+ ' . $presenter->context->parameters['website']['cacheValue'] . ' minutes';
						}
						$templateCache->save($key, $output, $parameters);
					}
				}
				echo $output;
			};
		}

		parent::beforeRender();
	}


	protected function createComponentHead()
	{
		$head = $this->getWidgetManager()->getWidget('head')->invoke();
		$head->setTitle($this->route->title);
		$head->setRobots($this->route->robots);
		$head->setKeywords($this->route->keywords);
		$head->setDescription($this->route->description);
		$head->setAuthor($this->route->author);
		return $head;
	}
}
