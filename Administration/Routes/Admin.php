<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace CmsModule\Administration\Routes;

use Venne;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class Admin extends \Nette\Application\Routers\Route
{

	/**
	 * Maps HTTP request to a Request object.
	 *
	 * @param  Nette\Http\IRequest
	 * @return Nette\Application\Request|NULL
	 */
	public function match(\Nette\Http\IRequest $httpRequest)
	{
		$data = parent::match($httpRequest);
		if ($data === NULL) {
			return NULL;
		}
		$presenter = explode(":", $data->presenterName);
		$presenter = $presenter[0] . ":Admin:" . implode(":", array_splice($presenter, 1));
		$data->setPresenterName($presenter);
		return $data;
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
		$data = parent::constructUrl($appRequest, $refUrl);
		$data = strtr($data, array(
			".admin/" => "/",
			".admin." => ".",
			"/admin." => "/",
			".Admin/" => "/",
			".Admin." => ".",
			"/Admin." => "/"
		));
		return $data;
	}

}
