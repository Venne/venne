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
use Nette\Utils\Strings;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 *
 * @ORM\MappedSuperclass
 *
 * @property-read PageEntity $page
 * @property-read ExtendedPageEntity $extendedPage
 * @property-read RouteEntity $route
 */
abstract class ExtendedRouteEntity extends IdentifiedEntity
{

	/**
	 * @var PageEntity
	 * @ORM\ManyToOne(targetEntity="\CmsModule\Content\Entities\PageEntity")
	 * @ORM\JoinColumn(referencedColumnName="id", onDelete="CASCADE")
	 */
	protected $page;

	/**
	 * @var ExtendedPageEntity
	 * @ORM\ManyToOne(targetEntity="\CmsModule\Blank")
	 * @ORM\JoinColumn(referencedColumnName="id", onDelete="CASCADE")
	 */
	protected $extendedPage;

	/**
	 * @var RouteEntity
	 * @ORM\OneToOne(targetEntity="\CmsModule\Content\Entities\RouteEntity", cascade={"persist"})
	 * @ORM\JoinColumn(referencedColumnName="id", onDelete="CASCADE")
	 */
	protected $route;

	/**
	 * @var LanguageEntity
	 */
	protected $locale;


	/**
	 * @param ExtendedPageEntity $page
	 */
	public function __construct(ExtendedPageEntity $page)
	{
		$this->extendedPage = $page;
		$this->page = $this->extendedPage->page;
		$this->page->routes[] = $this->route = new RouteEntity($this->page, get_class($this));
		$this->route->type = $this->getPresenterName();
		$this->route->params = $this->getPresenterParameters();
		$this->startup();
	}


	public function __toString()
	{
		return $this->getRoute()->getName();
	}


	protected function startup()
	{
	}


	public static function getPageName()
	{
		return static::getReflection()->getNamespaceName() . '\PageEntity';
	}


	/**
	 * @param string $name
	 * @return $this
	 */
	public function setName($name)
	{
		$this->getRoute()
			->setName($name)
			->setTitle($name)
			->setLocalUrl(Strings::webalize($name));

		return $this;
	}


	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->getRoute()->getName();
	}


	/**
	 * @return PageEntity
	 */
	public function getPage()
	{
		return $this->page;
	}


	/**
	 * @return ExtendedPageEntity
	 */
	public function getExtendedPage()
	{
		return $this->extendedPage;
	}


	/**
	 * @return RouteEntity
	 */
	public function getRoute()
	{
		return $this->route;
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
	protected function getPresenterName()
	{
		$ref = $this->getReflection();
		$ret = explode('\\', $ref->getNamespaceName());

		if (isset($ret[0]) && substr($ret[0], -6) === 'Module') {
			$ret[0] = substr($ret[0], 0, -6);
		}

		$name = $ref->getShortName();
		if (substr($name, -6) === 'Entity') {
			$name = substr($name, 0, -6);
		}
		$ret[] = $name;
		$ret[] = 'default';

		return join(':', $ret);
	}


	/**
	 * @return array
	 */
	protected function getPresenterParameters()
	{
		return array();
	}
}
