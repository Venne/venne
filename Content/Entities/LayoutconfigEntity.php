<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace CmsModule\Content\Entities;

use Venne;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 * @Entity(repositoryClass="\DoctrineModule\Repositories\BaseRepository")
 * @Table(name="layoutconfig")
 */
class LayoutconfigEntity extends \DoctrineModule\Entities\IdentifiedEntity
{


	/**
	 * @var RouteEntity[]
	 * @OneToMany(targetEntity="RouteEntity", mappedBy="layoutconfig")
	 */
	protected $routes;


	/**
	 * @param $type
	 */
	public function __construct(RouteEntity $routeEntity)
	{
	}


	/**
	 * @return RouteEntity[]
	 */
	public function getRoutes()
	{
		return $this->routes;
	}
}
