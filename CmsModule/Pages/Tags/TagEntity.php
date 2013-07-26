<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace CmsModule\Pages\Tags;

use CmsModule\Content\Entities\ExtendedRouteEntity;
use CmsModule\Content\Entities\RouteEntity;
use Doctrine\ORM\Mapping as ORM;
use Nette\Utils\Strings;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 * @ORM\Entity(repositoryClass="\CmsModule\Pages\Tags\TagRepository")
 * @ORM\Table(name="tags")
 */
class TagEntity extends ExtendedRouteEntity
{

	const CACHE = 'Cms.TagEntity';

	/**
	 * @var RouteEntity[]
	 * @ORM\ManyToMany(targetEntity="\CmsModule\Content\Entities\RouteEntity", mappedBy="tags")
	 **/
	protected $routes;

	/**
	 * @var string
	 * @ORM\Column(type="string", unique=true)
	 */
	protected $name;


	protected function startup()
	{
		parent::startup();

		$this->route->published = TRUE;
		$this->setName(Strings::random());
	}


	/**
	 * @return null|string
	 */
	public function __toString()
	{
		return $this->name;
	}


	/**
	 * @param RouteEntity[] $routes
	 */
	public function setRoutes($routes)
	{
		$this->routes = $routes;
	}


	/**
	 * @return RouteEntity[]
	 */
	public function getRoutes()
	{
		return $this->routes;
	}


	/**
	 * @param $name
	 */
	public function setName($name)
	{
		if ($this->name) {
			$this->route->generateDate();
		}

		$this->name = $name;
		$this->updateRoute();
	}


	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}


	protected function updateRoute()
	{
		$this->route->setValueForAllTranslations('name', $this->name);
		$this->route->setValueForAllTranslations('title', $this->name);
		$this->route->setValueForAllTranslations('localUrl', Strings::webalize($this->name));
	}
}

