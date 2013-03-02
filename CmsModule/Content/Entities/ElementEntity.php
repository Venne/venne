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
use CmsModule\Content\Elements\Helpers;
use Doctrine\ORM\Mapping as ORM;
use DoctrineModule\Entities\IdentifiedEntity;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 * @ORM\Entity(repositoryClass="\CmsModule\Content\Repositories\ElementRepository")
 * @ORM\Table(name="element", indexes={@ORM\index(name="name_idx", columns={"name"})})
 * @ORM\InheritanceType("JOINED")
 * @ORM\DiscriminatorColumn(name="type", type="string")
 * @ORM\DiscriminatorMap({"base" = "ElementEntity"})
 */
abstract class ElementEntity extends IdentifiedEntity
{

	const MODE_LAYOUT = 0;

	const MODE_PAGE = 1;

	const MODE_ROUTE = 2;

	/** @var array */
	protected static $modes = array(self::MODE_LAYOUT => 'Layout', self::MODE_PAGE => 'page', self::MODE_ROUTE => 'route');

	/**
	 * @var \CmsModule\Content\Entities\LayoutEntity
	 * @ORM\ManyToOne(targetEntity="\CmsModule\Content\Entities\LayoutEntity")
	 * @ORM\JoinColumn(onDelete="CASCADE")
	 */
	protected $layout;

	/**
	 * @var \CmsModule\Content\Entities\PageEntity
	 * @ORM\ManyToOne(targetEntity="\CmsModule\Content\Entities\PageEntity")
	 * @ORM\JoinColumn(onDelete="CASCADE")
	 */
	protected $page;

	/**
	 * @var \CmsModule\Content\Entities\RouteEntity
	 * @ORM\ManyToOne(targetEntity="\CmsModule\Content\Entities\RouteEntity")
	 * @ORM\JoinColumn(onDelete="CASCADE")
	 */
	protected $route;

	/**
	 * @var int
	 * @ORM\Column(type="string")
	 */
	protected $name;

	/**
	 * @var int
	 * @ORM\Column(type="string")
	 */
	protected $nameRaw;

	/**
	 * @var int
	 * @ORM\Column(type="integer")
	 */
	protected $mode;


	/**
	 * @param LayoutEntity $layoutEntity
	 * @param RouteEntity $route
	 */
	final public function setDefaults($name, LayoutEntity $layout, PageEntity $page = NULL, RouteEntity $route = NULL)
	{
		$this->nameRaw = $name;
		$this->name = Helpers::encodeName($name);
		$this->route = $route;
		$this->page = $page;
		$this->layout = $layout;

		$this->mode = self::MODE_LAYOUT;
	}


	/**
	 * @param int $mode
	 */
	public function setMode($mode)
	{
		if (!isset(self::$modes[$mode])) {
			throw new \Nette\InvalidArgumentException;
		}

		$this->mode = $mode;
	}


	/**
	 * @return int
	 */
	public function getMode()
	{
		return $this->mode;
	}


	/**
	 * @return array
	 */
	public static function getModes()
	{
		return self::$modes;
	}


	/**
	 * @param \CmsModule\Content\Entities\LayoutEntity $layout
	 */
	public function setLayout($layout)
	{
		$this->layout = $layout;
	}


	/**
	 * @return \CmsModule\Content\Entities\LayoutEntity
	 */
	public function getLayout()
	{
		return $this->layout;
	}


	/**
	 * @param int $name
	 */
	public function setName($name)
	{
		$this->name = $name;
	}


	/**
	 * @return int
	 */
	public function getName()
	{
		return $this->name;
	}


	/**
	 * @param int $nameRaw
	 */
	public function setNameRaw($nameRaw)
	{
		$this->nameRaw = $nameRaw;
	}


	/**
	 * @return int
	 */
	public function getNameRaw()
	{
		return $this->nameRaw;
	}


	/**
	 * @param \CmsModule\Content\Entities\PageEntity $page
	 */
	public function setPage($page)
	{
		$this->page = $page;
	}


	/**
	 * @return \CmsModule\Content\Entities\PageEntity
	 */
	public function getPage()
	{
		return $this->page;
	}


	/**
	 * @param \CmsModule\Content\Entities\RouteEntity $route
	 */
	public function setRoute($route)
	{
		$this->route = $route;
	}


	/**
	 * @return \CmsModule\Content\Entities\RouteEntity
	 */
	public function getRoute()
	{
		return $this->route;
	}
}
