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
use Doctrine\ORM\Mapping as ORM;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 * @ORM\Entity(repositoryClass="\DoctrineModule\Repositories\BaseRepository")
 * @ORM\Table(name="layoutconfig")
 */
class LayoutconfigEntity extends \DoctrineModule\Entities\IdentifiedEntity
{


	/**
	 * @var RouteEntity[]
	 * @ORM\OneToMany(targetEntity="RouteEntity", mappedBy="layoutconfig", cascade={"persist", "remove", "merge"})
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
