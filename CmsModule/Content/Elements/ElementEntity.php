<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace CmsModule\Content\Elements;

use CmsModule\Content\Entities\LanguageEntity;
use CmsModule\Content\Entities\LayoutEntity;
use CmsModule\Content\Entities\PageEntity;
use CmsModule\Content\Entities\RouteEntity;
use Doctrine\ORM\Mapping as ORM;
use DoctrineModule\Entities\IdentifiedEntity;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 * @ORM\Entity(repositoryClass="\CmsModule\Content\Repositories\ElementRepository")
 * @ORM\Table(name="element", indexes={@ORM\index(name="name_idx", columns={"name"})})
 */
class ElementEntity extends IdentifiedEntity
{

	const MODE_WEBSITE = 0;

	const MODE_LAYOUT = 1;

	const MODE_PAGE = 2;

	const MODE_ROUTE = 4;

	const LANGMODE_SHARE = 0;

	const LANGMODE_SPLIT = 1;

	/** @var array */
	protected static $modes = array(
		self::MODE_WEBSITE => 'Website',
		self::MODE_LAYOUT => 'Layout',
		self::MODE_PAGE => 'Page',
		self::MODE_ROUTE => 'Route',
	);

	/** @var array */
	protected static $langModes = array(
		self::LANGMODE_SHARE => 'Share',
		self::LANGMODE_SPLIT => 'Split',
	);

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
	 * @var \CmsModule\Content\Entities\LanguageEntity
	 * @ORM\ManyToOne(targetEntity="\CmsModule\Content\Entities\LanguageEntity")
	 * @ORM\JoinColumn(onDelete="CASCADE")
	 */
	protected $language;

	/**
	 * @var string
	 * @ORM\Column(type="string")
	 */
	protected $name;

	/**
	 * @var string
	 * @ORM\Column(type="string")
	 */
	protected $nameRaw;

	/**
	 * @var int
	 * @ORM\Column(type="integer")
	 */
	protected $mode = 0;

	/**
	 * @var int
	 * @ORM\Column(type="integer")
	 */
	protected $langMode = 0;

	/**
	 * @var string
	 * @ORM\Column(type="string")
	 */
	protected $class;


	/**
	 * @param $class
	 * @param $name
	 * @param LayoutEntity $layout
	 * @param PageEntity $page
	 * @param RouteEntity $route
	 * @param LanguageEntity $language
	 */
	public function __construct($class, $name, LayoutEntity $layout = NULL, PageEntity $page = NULL, RouteEntity $route = NULL, LanguageEntity $language = NULL)
	{
		$this->class = $class;
		$this->nameRaw = $name;
		$this->name = Helpers::encodeName($name);
		$this->route = $route;
		$this->page = $page;
		$this->layout = $layout;
		$this->language = $language;
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
	 * @return array
	 */
	public static function getLangModes()
	{
		return self::$langModes;
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


	/**
	 * @param int $langMode
	 */
	public function setLangMode($langMode)
	{
		$this->langMode = $langMode;
	}


	/**
	 * @return int
	 */
	public function getLangMode()
	{
		return $this->langMode;
	}


	/**
	 * @param \CmsModule\Content\Entities\LanguageEntity $language
	 */
	public function setLanguage($language)
	{
		$this->language = $language;
	}


	/**
	 * @return \CmsModule\Content\Entities\LanguageEntity
	 */
	public function getLanguage()
	{
		return $this->language;
	}


	/**
	 * @return string
	 */
	public function getClass()
	{
		return $this->class;
	}
}
