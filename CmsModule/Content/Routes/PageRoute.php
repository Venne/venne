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

use CmsModule\Content\Repositories\LanguageRepository;
use CmsModule\Content\Repositories\RouteRepository;
use Nette\Callback;
use DoctrineModule\Repositories\BaseRepository;
use Nette\Application\Routers\Route;
use CmsModule\Content\Entities\RouteEntity;
use CmsModule\Content\Entities\PageEntity;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class PageRoute extends Route
{


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

	/** @var \Nette\Caching\Cache */
	protected $_templateCache;


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
		$this->_templateCache = new \Nette\Caching\Cache($cache, \CmsModule\Content\Presenters\PagePresenter::CACHE_OUTPUT);

		$this->checkConnectionFactory = $checkConnectionFactory;
		$this->languages = $languages;
		$this->defaultLanguage = $defaultLanguage;

		parent::__construct($prefix . '<url .+>[/<module qwertzuiop>/<presenter qwertzuiop>]' . (count($this->languages) > 1 && strpos($prefix, '<lang>') === FALSE ? '?lang=<lang>' : ''), $parameters + array(
			'presenter' => self::DEFAULT_PRESENTER,
			'module' => self::DEFAULT_MODULE,
			'action' => self::DEFAULT_ACTION,
			'lang' => NULL,
			'url' => array(
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
		$key = $httpRequest->getUrl()->getAbsoluteUrl() . ($this->container->user->isLoggedIn() ? '|logged' : '');
		$output = $this->_templateCache->load($key);
		if ($output) {
			return new \Nette\Application\Request(
				'Cms:Cached',
				$httpRequest->getMethod(),
				array(),
				$httpRequest->getPost(),
				$httpRequest->getFiles(),
				array(\Nette\Application\Request::SECURED => $httpRequest->isSecured())
			);
		}

		if (!$this->checkConnectionFactory->invoke()) {
			return NULL;
		}

		if (($request = parent::match($httpRequest)) === NULL || !array_key_exists('url', $request->parameters)) {
			return NULL;
		}

		$parameters = $request->parameters;

		if (count($this->languages) > 1) {
			if (!isset($parameters['lang'])) {
				$parameters['lang'] = $this->defaultLanguage;
			}

			try {
				$route = $this->getRouteRepository()->createQueryBuilder('a')
					->leftJoin('a.page', 'm')
					->leftJoin('m.languages', 'p')
					->where('a.url = :url')
					->andWhere('a.published = :true')
					->andWhere('m.published = :true')
					->andWhere('p.alias = :lang')
					->andWhere('m.tag IS NULL')
					->setParameter('lang', $parameters['lang'])
					->setParameter('url', $parameters['url'])
					->setParameter('true', TRUE)
					->getQuery()->getSingleResult();
			} catch (\Doctrine\ORM\NoResultException $e) {
				return NULL;
			}
		} else {
			try {
				$route = $this->getRouteRepository()->createQueryBuilder('a')
					->leftJoin('a.page', 'm')
					->where('a.url = :url')
					->andWhere('a.published = :true')
					->andWhere('m.published = :true')
					->andWhere('m.tag IS NULL')
					->setParameter('url', $parameters['url'])
					->setParameter('true', TRUE)
					->getQuery()->getSingleResult();
			} catch (\Doctrine\ORM\NoResultException $e) {
				return NULL;
			}
		}

		return $this->modifyMatchRequest($request, $route, $parameters);
	}


	/**
	 * Modify request by page
	 *
	 * @param \Nette\Application\Request $appRequest
	 * @param PageEntity $page
	 * @return \Nette\Application\Request
	 */
	protected function modifyMatchRequest(\Nette\Application\Request $appRequest, RouteEntity $route, $parameters)
	{
		$parameters = $route->params + $parameters;
		$parameters['route'] = $route;
		$type = explode(':', $route->type);
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
	public function constructUrl(\Nette\Application\Request $appRequest, \Nette\Http\Url $refUrl)
	{
		if (!$this->checkConnectionFactory->invoke()) {
			return NULL;
		}

		$parameters = $appRequest->getParameters();

		if (isset($parameters['route'])) {
			$route = $parameters['route'];
			unset($parameters['route']);
		} else if (array_key_exists('url', $parameters)) {
			$url = $parameters['url'];
			if (count($this->languages) > 1) {
				if (!isset($parameters['lang'])) {
					$parameters['lang'] = $this->defaultLanguage;
				}
				try {
					$route = $this->getRouteRepository()->createQueryBuilder('a')
						->leftJoin('a.page', 'm')
						->leftJoin('m.languages', 'p')
						->where('a.url = :url')
						->andWhere('a.published = :true')
						->andWhere('m.published = :true')
						->andWhere('p.alias = :lang')
						->andWhere('m.tag IS NULL')
						->setParameter('url', $url)
						->setParameter('lang', $parameters['lang'])
						->setParameter('true', TRUE)
						->getQuery()->getSingleResult();
				} catch (\Doctrine\ORM\NoResultException $e) {
					return NULL;
				}
			} else {
				try {
					$route = $this->getRouteRepository()->createQueryBuilder('a')
						->leftJoin('a.page', 'm')
						->andWhere('a.url = :url')
						->andWhere('a.published = :true')
						->andWhere('m.published = :true')
						->andWhere('m.tag IS NULL')
						->setParameter('url', $url)
						->setParameter('true', TRUE)
						->getQuery()->getSingleResult();
				} catch (\Doctrine\ORM\NoResultException $e) {
					return NULL;
				}
			}
		} else {
			return NULL;
		}

		$this->modifyConstructRequest($appRequest, $route, $parameters);
		return parent::constructUrl($appRequest, $refUrl);
	}


	/**
	 * Modify request by page
	 *
	 * @param \Nette\Application\Request $request
	 * @param PageEntity $page
	 * @return \Nette\Application\Request
	 */
	protected function modifyConstructRequest(\Nette\Application\Request $request, RouteEntity $route, $parameters)
	{
		$request->setPresenterName(self::DEFAULT_MODULE . ':' . self::DEFAULT_PRESENTER);
		$request->setParameters(array(
			'module' => self::DEFAULT_MODULE,
			'presenter' => self::DEFAULT_PRESENTER,
			'action' => self::DEFAULT_ACTION,
			'lang' => isset($parameters['lang']) ? $parameters['lang'] : $route->page->languages[0]->alias,
			'url' => $route->url,
		) + $parameters);
		return $request;
	}
}
