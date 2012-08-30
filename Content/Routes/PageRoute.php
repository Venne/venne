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

use Nette\Object;
use Nette\Callback;
use DoctrineModule\Repositories\BaseRepository;
use Nette\Application\Routers\Route;
use CmsModule\Content\Entities\RouteEntity;
use CmsModule\Content\Entities\PageEntity;
use CmsModule\Content\ContentManager;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class PageRoute extends Route
{


	const DEFAULT_MODULE = "Cms";

	const DEFAULT_PRESENTER = "Base";

	const DEFAULT_ACTION = "default";

	/** @var Callback */
	protected $checkConnectionFactory;

	/** @var BaseRepository */
	protected $langRepository;

	/** @var BaseRepository */
	protected $routeRepository;

	/** @var ContentManager */
	protected $contentManager;

	/** @var bool */
	protected $languages;

	/** @var string */
	protected $defaultLanguage;


	/**
	 * Constructor
	 *
	 * @param ContentManager $contentManager
	 * @param BaseRepository $pageRepository
	 * @param BaseRepository $langRepository
	 * @param string $prefix
	 * @param bool $multilang
	 * @param string $defaultLangAlias
	 */
	public function __construct(Callback $checkConnectionFactory, ContentManager $contentManager, BaseRepository $routeRepository, BaseRepository $langRepository, $prefix, $parameters, $languages, $defaultLanguage)
	{
		$this->checkConnectionFactory = $checkConnectionFactory;
		$this->languages = $languages;
		$this->defaultLanguage = $defaultLanguage;
		$this->langRepository = $langRepository;
		$this->routeRepository = $routeRepository;
		$this->contentManager = $contentManager;

		parent::__construct($prefix . '<url .+>[/<module .+>/<presenter .+>]' . (strpos($prefix, '<lang>') === false ? '?lang=<lang>' : ''), $parameters + array(
			"presenter" => self::DEFAULT_PRESENTER,
			"module" => self::DEFAULT_MODULE,
			"action" => self::DEFAULT_ACTION,
			'lang' => NULL,
			"url" => array(
				self::VALUE => "",
				self::FILTER_IN => NULL,
				self::FILTER_OUT => NULL,
			)
		));
	}


	/**
	 * Maps HTTP request to a Request object.
	 *
	 * @param  Nette\Http\IRequest
	 * @return Nette\Application\Request|NULL
	 */
	public function match(\Nette\Http\IRequest $httpRequest)
	{
		if (!$this->checkConnectionFactory->invoke()) {
			return NULL;
		}

		$request = parent::match($httpRequest);

		if ($request === NULL || !array_key_exists("url", $request->parameters)) {
			return NULL;
		}

		$parameters = $request->parameters;

		// Search PageEntity
		if (count($this->languages) > 1) {
			if (!isset($parameters["lang"])) {
				$parameters["lang"] = $this->defaultLanguage;
			}

			try {
				$route = $this->routeRepository->createQueryBuilder("a")
					->leftJoin("a.page", "m")
					->leftJoin("m.languages", "p")
					->where("a.url = :url")
					->andWhere("p.alias = :lang")
					->setParameter("lang", $parameters["lang"])
					->setParameter("url", $parameters["url"])
					->getQuery()->getSingleResult();
			} catch (\Doctrine\ORM\NoResultException $e) {
				return NULL;
			}
		} else {
			try {
				$route = $this->routeRepository->createQueryBuilder("a")
					->where("a.url = :url")
					->setParameter("url", $parameters["url"])
					->getQuery()->getSingleResult();
			} catch (\Doctrine\ORM\NoResultException $e) {
				return NULL;
			}
		}

		// make request
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
		$parameters["route"] = $route;
		$parameters['cmsPage'] = 1;
		$type = explode(":", $route->type);
		$parameters["action"] = $type[count($type) - 1];
		$parameters["lang"] = $appRequest->parameters["lang"] ? : $this->defaultLanguage;
		unset($type[count($type) - 1]);
		$presenter = join(":", $type);
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

		while (true) {

			if (isset($parameters['route']) && $parameters['route'] instanceof RouteEntity) {
				$route = $parameters['route'];
				break;
			}

			if (isset($parameters['page']) && $parameters['page'] instanceof PageEntity) {
				$route = $parameters['page']->mainRoute;
				break;
			}

			return NULL;
		}

		unset($parameters['cmsPage']);
		unset($parameters['route']);
		unset($parameters['page']);

		// Search PageEntity
		if (isset($route)) {
		} elseif (count($this->languages) > 1) {
			if (!isset($parameters["lang"])) {
				$parameters["lang"] = $this->defaultLanguage;
			}
			try {
				$route = $this->routeRepository->createQueryBuilder("a")
					->leftJoin("a.page", "m")
					->leftJoin("m.languages", "p")
					->where("a.url = :url")
					->andWhere("p.alias = :lang")
					->setParameter("url", $url)
					->setParameter("lang", $parameters["lang"])
					->getQuery()->getSingleResult();
			} catch (\Doctrine\ORM\NoResultException $e) {
				return NULL;
			}
		} else {
			try {
				$route = $this->routeRepository->createQueryBuilder("a")
					->andWhere("a.url = :url")
					->setParameter("url", $url)
					->getQuery()->getSingleResult();
			} catch (\Doctrine\ORM\NoResultException $e) {
				return NULL;
			}
		}

		// make request
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
		$request->setPresenterName(self::DEFAULT_MODULE . ":" . self::DEFAULT_PRESENTER);
		$request->setParameters(array(
			'module' => self::DEFAULT_MODULE,
			'presenter' => self::DEFAULT_PRESENTER,
			'action' => self::DEFAULT_ACTION,
			'lang' => $route->page->languages[0]->alias,
			'url' => $route->url,
		) + $parameters);
		return $request;
	}
}
