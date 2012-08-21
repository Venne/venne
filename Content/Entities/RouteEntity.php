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
 * @Entity(repositoryClass="\DoctrineModule\ORM\BaseRepository")
 * @Table(name="route")
 */
class RouteEntity extends \DoctrineModule\ORM\BaseEntity
{

	const DEFAULT_LAYOUT = 'default';

	public static $robotsValues = array(
		"index, follow",
		"noindex, follow",
		"index, nofollow",
		"noindex, nofollow",
	);


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
	protected $layout;

	/** @Column(type="boolean") */
	protected $copyLayoutFromParent;


	/**
	 * @return string
	 */
	function __toString()
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
		$this->layout = 'default';
		$this->copyLayoutFromParent = true;

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

		$this->url = trim(($this->parent !== NULL ? $this->parent->url . "/" : "") . $this->localUrl, "/");

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
			$this->layout = $this->parent ? $this->parent->layout : self::DEFAULT_LAYOUT;
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
		if (!isset(self::$robotsValues[$robots])) {
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
		if ($this->copyLayoutFromParent) {
			return;
		}

		$this->layout = $layout;
		$this->generateLayouts();
	}


	public function getLayout()
	{
		return $this->layout;
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
}
