<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 *
 * Copyright (c) 2004, 2011 David Grudl (http://davidgrudl.com)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace CmsModule\Administration\Routes;

use Nette;

/**
 * The router broker.
 *
 * @author	 David Grudl
 * @author	   Josef Kříž
 */
class RouteList extends \Nette\Application\Routers\RouteList
{


	/** @var array */
	protected $cachedRoutes;

	/** @var string */
	protected $module;



	public function __construct($module = NULL)
	{
		$this->module = $module ? $module . ':' : '';
	}



	/**
	 * Maps HTTP request to a Request object.
	 *
	 * @param  Nette\Http\IRequest
	 * @return Nette\Application\Request|NULL
	 */
	public function match(Nette\Http\IRequest $httpRequest)
	{
		foreach ($this as $route) {
			$appRequest = $route->match($httpRequest);
			if ($appRequest !== NULL) {
				$presenter = explode(":", $appRequest->getPresenterName());
				$presenter = $presenter[0] . ":{$this->module}" . implode(":", array_splice($presenter, 2));
				$appRequest->setPresenterName($presenter);
				return $appRequest;
			}
		}
		return NULL;
	}



	/**
	 * Constructs absolute URL from Request object.
	 *
	 * @param  Nette\Application\Request
	 * @param  Nette\Http\Url
	 * @return string|NULL
	 */
	public function constructUrl(Nette\Application\Request $appRequest, Nette\Http\Url $refUrl)
	{
		if ($this->cachedRoutes === NULL) {
			$routes = array();
			$routes['*'] = array();

			foreach ($this as $route) {
				$presenter = $route instanceof \Nette\Application\Routers\Route ? $route->getTargetPresenter() : NULL;

				if ($presenter === FALSE) {
					continue;
				}

				if (is_string($presenter)) {
					$presenter = strtolower($presenter);
					if (!isset($routes[$presenter])) {
						$routes[$presenter] = $routes['*'];
					}
					$routes[$presenter][] = $route;
				} else {
					foreach ($routes as $id => $foo) {
						$routes[$id][] = $route;
					}
				}
			}

			$this->cachedRoutes = $routes;
		}

		if ($this->module) {
			$presenter2 = explode(":", $appRequest->getPresenterName());

			if (count($presenter2) < 2) {
				return NULL;
			}

			if (strncasecmp($tmp = $presenter2[1] . ":", $this->module, strlen($this->module)) === 0) {
				$appRequest = clone $appRequest;
				$appRequest->setPresenterName($presenter2[0] . ":" . ucfirst($this->module) . implode(":", array_slice($presenter2, 2)));
			} else {
				return NULL;
			}
		}

		$presenter = strtolower($appRequest->getPresenterName());
		if (!isset($this->cachedRoutes[$presenter])) {
			$presenter = '*';
		}

		foreach ($this->cachedRoutes[$presenter] as $route) {
			$url = $route->constructUrl($appRequest, $refUrl);
			if ($url !== NULL) {
				$module = substr($this->module, 0, -1);
				$url = str_replace("/{$module}.", "/", $url);
				$url = str_replace(".{$module}/", "/", $url);
				$url = str_replace(".{$module}.", ".", $url);
				return $url;
			}
		}

		return NULL;
	}

}
