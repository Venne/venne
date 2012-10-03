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
 * @Table(name="route")
 */
class RouteEntity extends \DoctrineModule\Entities\IdentifiedEntity
{

	const CACHE = 'Cms.RouteEntity';

	const DEFAULT_LAYOUT = 'default';

	const DEFAULT_CACHE_MODE = 'default';

	const CACHE_MODE_TIME = 'time';

	const CACHE_MODE_STATIC = 'static';

	protected static $robotsValues = array(
		'index, follow',
		'noindex, follow',
		'index, nofollow',
		'noindex, nofollow',
	);

	protected static $changefreqValues = array(
		'always',
		'hourly',
		'daily',
		'weekly',
		'monthly',
		'yearly',
		'never',
	);

	protected static $cacheModes = array(
		self::CACHE_MODE_TIME,
		self::CACHE_MODE_STATIC,
	);

	protected static $priorityValues = array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10);

	/**
	 * @Index
	 * @Column(type="string")
	 */
	protected $type;

	/**
	 * @Index
	 * @Column(type="string")
	 */
	protected $url;

	/** @Column(type="string") */
	protected $localUrl;

	/** @Column(type="string") */
	protected $params;

	/** @Column(type="integer") */
	protected $paramCounter;

	/**
	 * @var PageEntity
	 * @ManyToOne(targetEntity="PageEntity", inversedBy="routes")
	 * @JoinColumn(name="page_id", referencedColumnName="id", onDelete="CASCADE")
	 */
	protected $page;

	/**
	 * @var RouteEntity
	 * @ManyToOne(targetEntity="RouteEntity", inversedBy="childrens")
	 * @JoinColumn(name="route_id", referencedColumnName="id", onDelete="CASCADE")
	 */
	protected $parent;

	/**
	 * @var RouteEntity[]
	 * @OneToMany(targetEntity="RouteEntity", mappedBy="parent")
	 */
	protected $childrens;

	/**
	 * @var LayoutconfigEntity
	 * @ManyToOne(targetEntity="LayoutconfigEntity", inversedBy="routes", cascade={"persist"})
	 */
	protected $layoutconfig;

	/**
	 * @var LayoutconfigEntity
	 * @ManyToOne(targetEntity="LayoutconfigEntity", cascade={"persist"})
	 */
	protected $childrenLayoutconfig;


	/***************** Meta *******************/

	/** @Column(type="string") */
	protected $title;

	/** @Column(type="string") */
	protected $keywords;

	/** @Column(type="string") */
	protected $description;

	/** @Column(type="string") */
	protected $author;

	/** @Column(type="string") */
	protected $robots;

	/** @Column(type="string", nullable=true) */
	protected $changefreq;

	/** @Column(type="integer", nullable=true) */
	protected $priority;

	/** @Column(type="string", nullable=true) */
	protected $layout;

	/** @Column(type="string", nullable=true) */
	protected $childrenLayout;

	/** @Column(type="boolean") */
	protected $copyLayoutFromParent;

	/** @Column(type="string", nullable=true) */
	protected $cacheMode;

	/** @Column(type="boolean") */
	protected $copyCacheModeFromParent;

	/** @Column(type="boolean") */
	protected $copyLayoutToChildren;


	/**
	 * @return string
	 */
	public function __toString()
	{
		return $this->url;
	}


	/**
	 * @param $type
	 */
	public function __construct()
	{
		$this->type = '';
		$this->url = '';
		$this->localUrl = '';
		$this->params = json_encode(array());
		$this->paramCounter = 0;
		$this->childrens = new ArrayCollection;
		$this->layout = self::DEFAULT_LAYOUT;
		$this->cacheMode = self::DEFAULT_CACHE_MODE;
		$this->copyLayoutFromParent = true;
		$this->copyCacheModeFromParent = true;
		$this->copyLayoutToChildren = true;
		$this->layoutconfig = new LayoutconfigEntity($this);

		$this->title = '';
		$this->keywords = '';
		$this->description = '';
		$this->author = '';
		$this->robots = '';
	}


	/**
	 * @return string
	 */
	public function getUrl()
	{
		return $this->url;
	}


	/**
	 * @return string
	 */
	public function getLocalUrl()
	{
		return $this->localUrl;
	}


	/**
	 * @param bool $recursively
	 */
	public function generateUrl($recursively = true)
	{
		if ($this->parent !== NULL && method_exists($this->parent, "__load")) {
			$this->parent->__load();
		}

		if ($this->parent) {
			$this->url = trim(($this->parent !== NULL ? $this->parent->url . "/" : "") . $this->localUrl, "/");
		} else {
			$this->url = '';
		}

		if ($recursively) {
			foreach ($this->childrens as $children) {
				$children->generateUrl();
			}
		}
	}


	/**
	 * @param bool $recursively
	 */
	public function generateLayouts($recursively = true)
	{
		if ($this->parent !== NULL && method_exists($this->parent, "__load")) {
			$this->parent->__load();
		}

		if ($this->copyLayoutFromParent) {
			$this->layout = $this->parent ? ($this->parent->copyLayoutToChildren ? $this->parent->layout : $this->parent->childrenLayout) : self::DEFAULT_LAYOUT;
			$this->layoutconfig = $this->parent ? ($this->parent->copyLayoutToChildren ? $this->parent->layoutconfig : $this->parent->childrenLayoutconfig) : new LayoutconfigEntity($this);
		} else {
			$this->layoutconfig = new LayoutconfigEntity($this);
		}

		if (!$this->copyLayoutToChildren) {
			$this->childrenLayoutconfig = new LayoutconfigEntity($this);
		} else {
			$this->childrenLayoutconfig = NULL;
		}

		if ($this->copyCacheModeFromParent) {
			$this->cacheMode = $this->parent ? $this->parent->cacheMode : self::DEFAULT_CACHE_MODE;
		}

		if ($recursively) {
			foreach ($this->childrens as $children) {
				$children->generateLayouts();
			}
		}
	}


	/**
	 * @param $localUrl
	 */
	public function setLocalUrl($localUrl)
	{
		$this->localUrl = $localUrl;
		$this->generateUrl();
	}


	/**
	 * @return array
	 */
	public function getParams()
	{
		return (array)json_decode($this->params);
	}


	/**
	 * @param $params
	 */
	public function setParams($params)
	{
		$delete = array("module", "presenter", "action");
		foreach ($delete as $item) {
			if (isset($params[$item])) {
				unset($params[$item]);
			}
		}

		ksort($params);
		$this->params = json_encode($params);
		$this->paramCounter = count($params);
	}


	/**
	 * @return mixed
	 */
	public function getType()
	{
		return $this->type;
	}


	/**
	 * @param $type
	 */
	public function setType($type)
	{
		$this->type = $type;
	}


	/**
	 * @return mixed
	 */
	public function getParent()
	{
		return $this->parent;
	}


	/**
	 * @param $parent
	 */
	public function setParent(RouteEntity $parent = NULL)
	{
		if ($this->parent == $parent) {
			return;
		}

		$this->parent = $parent;
		$this->generateUrl();
		$this->generateLayouts();
	}


	/**
	 * @param $childrens
	 */
	public function setChildrens($childrens)
	{
		$this->childrens = $childrens;
	}


	/**
	 * @return ArrayCollection
	 */
	public function getChildrens()
	{
		return $this->childrens;
	}


	public function getPage()
	{
		return $this->page;
	}


	public function setPage($page)
	{
		$this->page = $page;
	}


	public function setAuthor($author)
	{
		$this->author = $author;
	}


	public function getAuthor()
	{
		return $this->author;
	}


	public function setKeywords($keywords)
	{
		$this->keywords = $keywords;
	}


	public function getKeywords()
	{
		return $this->keywords;
	}


	public function setRobots($robots)
	{
		if (array_search($robots, self::$robotsValues) === false) {
			throw new \Nette\InvalidArgumentException;
		}

		$this->robots = $robots;
	}


	public function getRobots()
	{
		return $this->robots;
	}


	public function setTitle($title)
	{
		$this->title = $title;
	}


	public function getTitle()
	{
		return $this->title;
	}


	public function setLayout($layout)
	{
		if ($this->layout === $layout || $this->copyLayoutFromParent) {
			return;
		}

		$this->layout = $layout;
		$this->generateLayouts();
	}


	public function getLayout()
	{
		return $this->layout;
	}


	public function setChildrenLayout($childrenLayout)
	{
		if ($this->childrenLayout === $childrenLayout || $this->copyLayoutToChildren) {
			return;
		}

		$this->childrenLayout = $childrenLayout;
		$this->generateLayouts();
	}


	public function getChildrenLayout()
	{
		return $this->childrenLayout;
	}


	public function setDescription($description)
	{
		$this->description = $description;
	}


	public function getDescription()
	{
		return $this->description;
	}


	public function setCopyLayoutFromParent($copyLayoutFromParent)
	{
		if ($this->copyLayoutFromParent == $copyLayoutFromParent) {
			return;
		}

		$this->copyLayoutFromParent = $copyLayoutFromParent;
		$this->generateLayouts();
	}


	public function getCopyLayoutFromParent()
	{
		return $this->copyLayoutFromParent;
	}


	public function setCopyLayoutToChildren($copyLayoutToChildren)
	{
		if ($this->copyLayoutToChildren == $copyLayoutToChildren) {
			return;
		}

		$this->copyLayoutToChildren = $copyLayoutToChildren;
		$this->generateLayouts();
	}


	public function getCopyLayoutToChildren()
	{
		return $this->copyLayoutToChildren;
	}


	public function setChangefreq($changefreq)
	{
		if ($changefreq !== NULL && array_search($changefreq, self::$changefreqValues) === false) {
			throw new \Nette\InvalidArgumentException;
		}

		$this->changefreq = $changefreq;
	}


	public function getChangefreq()
	{
		return $this->changefreq;
	}


	public function setPriority($priority)
	{
		$priority = (int)$priority;

		if (!is_integer($priority) || $priority < 0 || $priority > 10) {
			throw new \Nette\InvalidArgumentException;
		}

		$this->priority = $priority;
	}


	public function getPriority()
	{
		return $this->priority;
	}


	/**
	 * @param \CmsModule\Content\Entities\LayoutconfigEntity $layoutconfig
	 */
	public function setLayoutconfig($layoutconfig)
	{
		$this->layoutconfig = $layoutconfig;
	}


	/**
	 * @return \CmsModule\Content\Entities\LayoutconfigEntity
	 */
	public function getLayoutconfig()
	{
		return $this->layoutconfig;
	}


	/**
	 * @param \CmsModule\Content\Entities\LayoutconfigEntity $childrenLayoutconfig
	 */
	public function setChildrenLayoutconfig($childrenLayoutconfig)
	{
		$this->childrenLayoutconfig = $childrenLayoutconfig;
	}


	/**
	 * @return \CmsModule\Content\Entities\LayoutconfigEntity
	 */
	public function getChildrenLayoutconfig()
	{
		return $this->childrenLayoutconfig;
	}


	public function setCopyCacheModeFromParent($copyCacheModeFromParent)
	{
		$this->copyCacheModeFromParent = $copyCacheModeFromParent;
	}


	public function getCopyCacheModeFromParent()
	{
		return $this->copyCacheModeFromParent;
	}


	public function setCacheMode($cacheMode)
	{
		if ($this->cacheMode == $cacheMode || $this->copyCacheModeFromParent) {
			return;
		}

		$this->cacheMode = $cacheMode;
		$this->generateLayouts();
	}


	public function getCacheMode()
	{
		return $this->cacheMode;
	}


	public static function getChangefreqValues()
	{
		return self::$changefreqValues;
	}


	public static function getPriorityValues()
	{
		return self::$priorityValues;
	}


	public static function getRobotsValues()
	{
		return self::$robotsValues;
	}


	public static function getCacheModes()
	{
		return self::$cacheModes;
	}
}
