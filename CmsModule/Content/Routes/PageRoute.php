<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace CmsModule\Content\Routes;

use CmsModule\Content\Entities\ExtendedRouteEntity;
use CmsModule\Content\Entities\PageEntity;
use CmsModule\Content\Entities\RouteEntity;
use CmsModule\Content\Repositories\LanguageRepository;
use CmsModule\Content\Repositories\RouteRepository;
use Doctrine\ORM\NoResultException;
use DoctrineModule\Repositories\BaseRepository;
use Nette\Application\Request;
use Nette\Application\Routers\Route;
use Nette\Caching\Cache;
use Nette\Callback;
use Nette\Http\Url;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class PageRoute extends Route
{

	const CACHE = 'Venne.routing';

	const DEFAULT_MODULE = 'Cms';

	const DEFAULT_PRESENTER = 'Base';

	const DEFAULT_ACTION = 'default';

	/** @var \Nette\DI\Container|\SystemContainer */
	protected $container;

	/** @var Callback */
	protected $checkConnectionFactory;

	/** @var bool */
	protected $languages;

	/** @var string */
	protected $defaultLanguage;

	/** @var bool */
	protected $_defaultLang = FALSE;

	/** @var Cache */
	private $cache;


	/**
	 * @param Callback $checkConnectionFactory
	 * @param BaseRepository $routeRepository
	 * @param BaseRepository $langRepository
	 * @param $prefix
	 * @param $parameters
	 * @param $languages
	 * @param $defaultLanguage
	 */
	public function __construct(\Nette\DI\Container $container, \Nette\Caching\IStorage $cache, Callback $checkConnectionFactory, $prefix, $parameters, $languages, $defaultLanguage, $oneWay = FALSE)
	{
		$this->container = $container;
		$this->cache = new Cache($cache, self::CACHE);

		$this->checkConnectionFactory = $checkConnectionFactory;
		$this->languages = $languages;
		$this->defaultLanguage = $defaultLanguage;

		parent::__construct($prefix . '<slug .+>[/<module qwertzuiop>/<presenter qwertzuiop>]' . (count($this->languages) > 1 && strpos($prefix, '<lang>') === FALSE ? '?lang=<lang>' : ''), $parameters + array(
				'presenter' => self::DEFAULT_PRESENTER,
				'module' => self::DEFAULT_MODULE,
				'action' => self::DEFAULT_ACTION,
				'lang' => NULL,
				'slug' => array(
					self::VALUE => '',
					self::FILTER_IN => NULL,
					self::FILTER_OUT => NULL,
				)
			), $oneWay ? Route::ONE_WAY : NULL);
	}


	/**
	 * @return LanguageRepository
	 */
	protected function getLangRepository()
	{
		return $this->container->cms->languageRepository;
	}


	/**
	 * @return RouteRepository
	 */
	protected function getRouteRepository()
	{
		return $this->container->cms->routeRepository;
	}


	/**
	 * Maps HTTP request to a Request object.
	 *
	 * @param  Nette\Http\IRequest
	 * @return \Nette\Application\Request|NULL
	 */
	public function match(\Nette\Http\IRequest $httpRequest)
	{
		if (($request = parent::match($httpRequest)) === NULL || !array_key_exists('slug', $request->parameters)) {
			return NULL;
		}


		if (!$this->checkConnectionFactory->invoke()) {
			return NULL;
		}

		$parameters = $request->parameters;

		if (!$this->_defaultLang && count($this->languages) > 1) {
			if (!isset($parameters['lang'])) {
				$parameters['lang'] = $this->defaultLanguage;
			}

			if ($parameters['lang'] !== $this->defaultLanguage) {
				$this->container->cms->pageListener->setLocale($parameters['lang']);
			}

			$this->_defaultLang = TRUE;
		}

		$key = array($httpRequest->getUrl()->getAbsoluteUrl(), $parameters['lang']);
		$data = $this->cache->load($key);
		if ($data) {
			return $this->modifyMatchRequest($request, $data[0], $data[1], $data[2], $data[3], $parameters);
		}

		if (count($this->languages) > 1) {
			try {
				$tr = $this->container->entityManager->getRepository('CmsModule\Content\Entities\RouteTranslationEntity')->createQueryBuilder('a')
					->leftJoin('a.language', 'l')
					->andWhere('a.url = :url')->setParameter('url', $parameters['slug'])
					->andWhere('l.alias = :lang')->setParameter('lang', $parameters['lang'])
					->getQuery()->getSingleResult();
			} catch (NoResultException $e) {
			}

			try {
				if (!isset($tr) || !$tr) {
					$route = $this->getRouteRepository()->createQueryBuilder('a')
						->leftJoin('a.language', 'p')
						->andWhere('a.language IS NULL OR p.alias = :lang')->setParameter('lang', $parameters['lang'])
						->andWhere('a.url = :url')->setParameter('url', $parameters['slug'])
						->getQuery()->getSingleResult();
				} else {
					$route = $this->getRouteRepository()->createQueryBuilder('a')
						->leftJoin('a.translations', 't')
						->where('t.id = :id')->setParameter('id', $tr->id)
						->getQuery()->getSingleResult();
				}
			} catch (NoResultException $e) {
				return NULL;
			}
		} else {
			try {
				$route = $this->getRouteRepository()->createQueryBuilder('a')
					->where('a.url = :url')
					->setParameter('url', $parameters['slug'])
					->getQuery()->getSingleResult();
			} catch (NoResultException $e) {
				return NULL;
			}
		}

		$this->cache->save($key, array($route->id, $route->page->id, $route->type, $route->params), array(
			Cache::TAGS => array(RouteEntity::CACHE),
		));
		return $this->modifyMatchRequest($request, $route, $route->page, $route->type, $route->params, $parameters);
	}


	/**
	 * Modify request by page
	 *
	 * @param \Nette\Application\Request $appRequest
	 * @param RouteEntity $route
	 * @param PageEntity $page
	 * @param string $slug
	 * @return \Nette\Application\Request
	 */
	protected function modifyMatchRequest(\Nette\Application\Request $appRequest, $route, $page, $routeType, $routeParameters, $parameters)
	{
		if (is_object($route)) {
			$parameters['routeId'] = $route->id;
			$parameters['_route'] = $route;
		} else {
			$parameters['routeId'] = $route;
		}

		if (is_object($page)) {
			$parameters['pageId'] = $page->id;
			$parameters['_page'] = $page;
		} else {
			$parameters['pageId'] = $page;
		}

		$parameters = $routeParameters + $parameters;
		$type = explode(':', $routeType);
		$parameters['action'] = $type[count($type) - 1];
		$parameters['lang'] = $appRequest->parameters['lang'] ? : $this->defaultLanguage;
		unset($type[count($type) - 1]);
		$presenter = join(':', $type);
		$appRequest->setParameters($parameters);
		$appRequest->setPresenterName($presenter);
		return $appRequest;
	}


	/**
	 * Constructs absolute URL from Request object.
	 *
	 * @param  Nette\Application\Request
	 * @param  Nette\Http\Url
	 * @return string|NULL
	 */
	public function constructUrl(Request $appRequest, Url $refUrl)
	{
		if (!$this->checkConnectionFactory->invoke()) {
			return NULL;
		}

		$parameters = $appRequest->getParameters();
		$key = (array)$parameters;
		unset($key['_route']);
		unset($key['_page']);
		if (isset($key['route']) && is_object($key['route'])) {
			$key['route'] = $key['route'] instanceof RouteEntity ? $key['route']->id : $key['route']->route->id;
		}
		unset($key['page']);

		$data = $this->cache->load($key);
		if ($data) {
			return $data;
		}

		if (isset($parameters['special'])) {
			$special = $parameters['special'];
			unset($parameters['special']);
			if (count($this->languages) > 1) {
				if (!isset($parameters['lang'])) {
					$parameters['lang'] = $this->defaultLanguage;
				}

				try {
					if ($special === 'main') {
						$route = $this->getRouteRepository()->createQueryBuilder('a')
							->leftJoin('a.page', 'm')
							->leftJoin('m.language', 'p')
							->andWhere('p.alias = :lang OR a.language IS NULL')
							->andWhere('m.mainRoute = a.id AND a.url = :url')
							->setParameter('lang', $parameters['lang'])
							->setParameter('url', '')
							->getQuery()->getSingleResult();
					} else {
						$route = $this->getRouteRepository()->createQueryBuilder('a')
							->leftJoin('a.page', 'm')
							->leftJoin('m.language', 'p')
							->where('m.special = :special')
							->andWhere('p.alias = :lang OR a.language IS NULL')
							->andWhere('m.mainRoute = a.id')
							->setParameter('special', $special)
							->setParameter('lang', $parameters['lang'])
							->getQuery()->getSingleResult();
					}
				} catch (NoResultException $e) {
					return NULL;
				}
			} else {
				try {
					if ($special === 'main') {
						$route = $this->getRouteRepository()->createQueryBuilder('a')
							->andWhere('a.url = :url')
							->setParameter('url', '')
							->getQuery()->getSingleResult();
					} else {
						$route = $this->getRouteRepository()->createQueryBuilder('a')
							->leftJoin('a.page', 'm')
							->andWhere('m.special = :special')
							->andWhere('m.mainRoute = a.id')
							->setParameter('special', $special)
							->getQuery()->getSingleResult();
					}
				} catch (NoResultException $e) {
					return NULL;
				}
			}
			$route = $route->id;
		} elseif (isset($parameters['route'])) {
			$route = is_object($parameters['route'])
				? ($parameters['route'] instanceof ExtendedRouteEntity ? $parameters['route']->route->id : $parameters['route']->id)
				: $parameters['route'];
			unset($parameters['route']);
		} elseif (isset($parameters['_route'])) {
			$route = $parameters['_route']->id;
		} elseif (isset($parameters['routeId'])) {
			$route = $parameters['routeId'];
		} else {
			return NULL;
		}

		unset($parameters['_route']);
		unset($parameters['_page']);
		unset($parameters['routeId']);
		unset($parameters['pageId']);

		$this->modifyConstructRequest($appRequest, $this->getRouteRepository()->find($route), $parameters);

		$data = parent::constructUrl($appRequest, $refUrl);
		$this->cache->save($key, $data, array(
			Cache::TAGS => array(RouteEntity::CACHE),
		));
		return $data;
	}


	/**
	 * Modify request by page
	 *
	 * @param \Nette\Application\Request $request
	 * @param RouteEntity $route
	 * @param $parameters
	 * @return \Nette\Application\Request
	 */
	protected function modifyConstructRequest(Request $request, RouteEntity $route, $parameters)
	{
		$request->setPresenterName(self::DEFAULT_MODULE . ':' . self::DEFAULT_PRESENTER);
		$request->setParameters(array(
				'module' => self::DEFAULT_MODULE,
				'presenter' => self::DEFAULT_PRESENTER,
				'action' => self::DEFAULT_ACTION,
				'lang' => isset($parameters['lang']) ? $parameters['lang'] : ($route->page->language ? $route->page->language->alias : $this->defaultLanguage),
				'slug' => $route->getUrl(),
			) + $parameters);
		return $request;
	}
}
