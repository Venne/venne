<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace CmsModule\Components;

use CmsModule\Content\Control;
use CmsModule\Content\Entities\ExtendedRouteEntity;
use CmsModule\Content\Entities\RouteEntity;
use Nette\InvalidArgumentException;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class ItemControl extends Control
{

	public function renderDefault($route = NULL)
	{
		$this->template->route = (is_array($route) && isset($route[0]) && ($route[0] instanceof RouteEntity || $route[0] instanceof ExtendedRouteEntity)) ? $route[0] : $route;
	}
}
