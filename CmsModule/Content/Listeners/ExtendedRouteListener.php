<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace CmsModule\Content\Listeners;

use CmsModule\Content\Entities\RouteEntity;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Mapping as ORM;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class ExtendedRouteListener
{


	/** @ORM\PostLoad */
	public function postLoadHandler(RouteEntity $route, LifecycleEventArgs $event)
	{
		$em = $event->getEntityManager();
		$route->setExtendedRouteCallback(function () use ($em, $route) {
			return $em->getRepository($route->getClass())->findOneBy(array('route' => $route->id));
		});
	}

}
