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
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 * @ORM\Entity(repositoryClass="\CmsModule\Content\Repositories\RouteRepository")
 * @ORM\Table(name="route", indexes={
 * @ORM\Index(name="type_idx", columns={"type"}),
 * @ORM\Index(name="url_idx", columns={"url"}),
 * })
 */
class RouteEntity extends \DoctrineModule\Entities\IdentifiedEntity
{

	const CACHE = 'Cms.RouteEntity';

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
	 * @ORM\Column(type="string")
	 */
	protected $type;

	/**
	 * @ORM\Column(type="string")
	 */
	protected $url;

	/** @ORM\Column(type="string") */
	protected $localUrl;

	/** @ORM\Column(type="string") */
	protected $params;

	/** @ORM\Column(type="integer") */
	protected $paramCounter;

	/**
	 * @var PageEntity
	 * @ORM\ManyToOne(targetEntity="PageEntity", inversedBy="routes")
	 * @ORM\JoinColumn(name="page_id", referencedColumnName="id", onDelete="CASCADE")
	 */
	protected $page;

	/**
	 * @var RouteEntity
	 * @ORM\ManyToOne(targetEntity="RouteEntity", inversedBy="children")
	 * @ORM\JoinColumn(name="route_id", referencedColumnName="id", onDelete="CASCADE")
	 */
	protected $parent;

	/**
	 * @var RouteEntity[]
	 * @ORM\OneToMany(targetEntity="RouteEntity", mappedBy="parent", fetch="EXTRA_LAZY")
	 */
	protected $children;

	/**
	 * @var LayoutEntity
	 * @ORM\ManyToOne(targetEntity="LayoutEntity", inversedBy="routes", cascade={"persist"})
	 * @ORM\JoinColumn(onDelete="SET NULL")
	 */
	protected $layout;

	/**
	 * @var LayoutEntity
	 * @ORM\ManyToOne(targetEntity="LayoutEntity", cascade={"persist"})
	 * @ORM\JoinColumn(onDelete="SET NULL")
	 */
	protected $childrenLayout;

	/**
	 * @var bool
	 * @ORM\Column(type="boolean")
	 */
	protected $published = TRUE;


	/***************** Meta *******************/

	/** @ORM\Column(type="string") */
	protected $title;

	/** @ORM\Column(type="string") */
	protected $keywords;

	/** @ORM\Column(type="string") */
	protected $description;

	/** @ORM\Column(type="string") */
	protected $author;

	/** @ORM\Column(type="string") */
	protected $robots;

	/** @ORM\Column(type="string", nullable=true) */
	protected $changefreq;

	/** @ORM\Column(type="integer", nullable=true) */
	protected $priority;

	/** @ORM\Column(type="boolean") */
	protected $copyLayoutFromParent;

	/** @ORM\Column(type="string", nullable=true) */
	protected $cacheMode;

	/** @ORM\Column(type="boolean") */
	protected $copyCacheModeFromParent;

	/** @ORM\Column(type="boolean") */
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
		$this->children = new ArrayCollection;
		$this->cacheMode = self::DEFAULT_CACHE_MODE;
		$this->copyLayoutFromParent = TRUE;
		$this->copyCacheModeFromParent = TRUE;
		$this->copyLayoutToChildren = TRUE;

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
	public function generateUrl($recursively = TRUE)
	{
		if ($this->parent !== NULL && method_exists($this->parent, "__load")) {
			$this->parent->__load();
		}

		if ($this->parent) {
			$this->url = trim($this->parent->url . '/' . $this->localUrl, '/');
		} else {
			$this->url = '';
		}

		if ($recursively) {
			foreach ($this->children as $children) {
				$children->generateUrl();
			}
		}
	}


	/**
	 * @param bool $recursively
	 */
	public function generateLayouts($recursively = TRUE)
	{
		if ($this->parent !== NULL && method_exists($this->parent, "__load")) {
			$this->parent->__load();
		}

		if ($this->copyLayoutFromParent) {
			$this->layout = $this->parent ? ($this->parent->copyLayoutToChildren ? $this->parent->layout : $this->parent->childrenLayout) : NULL;
		}

		if ($this->copyLayoutToChildren) {
			$this->childrenLayout = $this->layout;
		}

		if ($this->copyCacheModeFromParent) {
			$this->cacheMode = $this->parent ? $this->parent->cacheMode : self::DEFAULT_CACHE_MODE;
		}

		if ($recursively) {
			foreach ($this->children as $children) {
				$children->generateLayouts();
			}
		}
	}


	/**
	 * @param $localUrl
	 */
	public function setLocalUrl($localUrl, $recursively = TRUE)
	{
		$this->localUrl = $localUrl;
		$this->generateUrl($recursively);
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
		$delete = array('module', 'presenter', 'action');
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
		if ($parent) {
			$parent->children[] = $this;
		}

		$this->generateUrl();
		$this->generateLayouts();
	}


	/**
	 * @param $children
	 */
	public function setChildren($children)
	{
		$this->children = $children;
	}


	/**
	 * @return ArrayCollection
	 */
	public function getChildren()
	{
		return $this->children;
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
		if (array_search($robots, self::$robotsValues) === FALSE) {
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


	public function setLayout(LayoutEntity $layout = NULL)
	{
		if ($layout === NULL && $this->layout === NULL) {
			return;
		}

		if ($layout && $this->layout && $layout->id == $this->layout->id) {
			return;
		}

		$this->layout = $layout;
		$this->generateLayouts();
	}


	public function getLayout()
	{
		return $this->layout;
	}


	public function setChildrenLayout(LayoutEntity $childrenLayout = NULL)
	{
		if ($childrenLayout === NULL && $this->childrenLayout === NULL) {
			return;
		}

		if ($childrenLayout && $this->childrenLayout && $childrenLayout->id == $this->childrenLayout->id) {
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
		if ($changefreq !== NULL && array_search($changefreq, self::$changefreqValues) === FALSE) {
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


	/**
	 * @param boolean $published
	 */
	public function setPublished($published)
	{
		$this->published = $published;
	}


	/**
	 * @return boolean
	 */
	public function getPublished()
	{
		return $this->published;
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
