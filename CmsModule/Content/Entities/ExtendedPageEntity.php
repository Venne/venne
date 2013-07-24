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

use Doctrine\ORM\Mapping as ORM;
use DoctrineModule\Entities\IdentifiedEntity;
use Nette\InvalidArgumentException;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 *
 * @ORM\MappedSuperclass
 *
 * @property-read PageEntity $page
 */
abstract class ExtendedPageEntity extends IdentifiedEntity
{

	/**
	 * @var PageEntity
	 * @ORM\ManyToOne(targetEntity="\CmsModule\Content\Entities\PageEntity", cascade={"ALL"})
	 * @ORM\JoinColumn(name="page_id", referencedColumnName="id", onDelete="CASCADE")
	 */
	protected $page;

	/**
	 * @var ExtendedRouteEntity
	 * @ORM\OneToOne(targetEntity="\CmsModule\Blank", cascade={"persist"})
	 * @ORM\JoinColumn(referencedColumnName="id", onDelete="CASCADE")
	 */
	protected $extendedMainRoute;

	/**
	 * @var LanguageEntity
	 */
	protected $locale;


	public function __construct()
	{
		$this->page = $this->createPageEntity();
		$this->extendedMainRoute = $this->createMainRoute();
		$this->page->mainRoute = $this->extendedMainRoute->getRoute();
		$this->page->special = $this->getSpecial();
		$this->startup();
	}


	public function __toString()
	{
		return (string)$this->getExtendedMainRoute();
	}


	protected function startup()
	{
	}


	/**
	 * @return PageEntity
	 */
	public function getPage()
	{
		return $this->page;
	}


	/**
	 * @return ExtendedRouteEntity
	 */
	public function getExtendedMainRoute()
	{
		return $this->extendedMainRoute;
	}


	/**
	 * @return ExtendedRouteEntity
	 */
	private function createMainRoute()
	{
		return $this->createRoute(static::getMainRouteName());
	}


	/**
	 * @param string $class
	 * @return ExtendedRouteEntity
	 */
	protected function createRoute($class)
	{
		$route = new $class($this);
		if (!$route instanceof ExtendedRouteEntity) {
			throw new InvalidArgumentException("Class '{$class}' is not instance of 'Cms\Content\Entities\ExtendedRouteEntity'.");
		}
		return $route;
	}


	/**
	 * @return PageEntity
	 */
	private function createPageEntity()
	{
		return new PageEntity($this);
	}


	/**
	 * @return string
	 */
	public static function getMainRouteName()
	{
		return static::getReflection()->getNamespaceName() . '\RouteEntity';
	}


	/**
	 * @param LanguageEntity $locale
	 */
	public function setLocale(LanguageEntity $locale = NULL)
	{
		$this->locale = $locale;
	}


	/**
	 * @return LanguageEntity
	 */
	public function getLocale()
	{
		return $this->locale;
	}


	/**
	 * @return string
	 */
	protected function getSpecial()
	{
		return NULL;
	}
}
