<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace CmsModule\Components;

use CmsModule\Content\Control;
use CmsModule\Content\Presenters\PagePresenter;
use CmsModule\Content\WebsiteManager;
use Nette\ComponentModel\IContainer;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class HeadControl extends Control
{

	const ROBOTS_INDEX = 1;

	const ROBOTS_NOINDEX = 2;

	const ROBOTS_FOLLOW = 4;

	const ROBOTS_NOFOLLOW = 8;

	/** @var string */
	private $keywords;

	/** @var string */
	private $description;

	/** @var string */
	private $robots;

	/** @var string */
	private $websiteName;

	/** @var string */
	private $author;

	/** @var string */
	private $title;

	/** @var string */
	private $titleTemplate;

	/** @var string */
	private $titleSeparator;

	/** @var array */
	private $feeds = array();

	/** @var WebsiteManager */
	private $websiteManager;


	/**
	 * @param WebsiteManager $websiteManager
	 */
	public function __construct(WebsiteManager $websiteManager)
	{
		parent::__construct();

		$this->websiteManager = $websiteManager;
	}


	protected function startup()
	{
		parent::startup();

		$this->websiteName = $this->websiteManager->name;
		$this->titleTemplate = $this->websiteManager->title;
		$this->titleSeparator = $this->websiteManager->titleSeparator;
		$this->author = $this->websiteManager->author;
		$this->keywords = $this->websiteManager->keywords;
		$this->description = $this->websiteManager->description;
	}


	/***************************** Setters/getters ************************************************/

	/**
	 * @param string $websiteName
	 */
	public function setWebsiteName($websiteName)
	{
		$this->websiteName = $websiteName;
	}


	/**
	 * @return string
	 */
	public function getWebsiteName()
	{
		return $this->websiteName;
	}


	/**
	 * @param string $author
	 */
	public function setAuthor($author)
	{
		$this->author = $author;
	}


	/**
	 * @return string
	 */
	public function getAuthor()
	{
		return $this->author;
	}


	/**
	 * @param string $description
	 */
	public function setDescription($description)
	{
		$this->description = $description;
	}


	/**
	 * @return string
	 */
	public function getDescription()
	{
		return $this->description;
	}


	/**
	 * @param string $keywords
	 */
	public function setKeywords($keywords)
	{
		$this->keywords = $keywords;
	}


	/**
	 * @return string
	 */
	public function getKeywords()
	{
		return $this->keywords;
	}


	/**
	 * @param string $robots
	 */
	public function setRobots($robots)
	{
		$this->robots = $robots;
	}


	/**
	 * @return string
	 */
	public function getRobots()
	{
		return $this->robots;
	}


	/**
	 * @param string $title
	 */
	public function setTitle($title)
	{
		$this->title = $title;
	}


	/**
	 * @return string
	 */
	public function getTitle()
	{
		return $this->title;
	}


	/**
	 * @param string $titleSeparator
	 */
	public function setTitleSeparator($titleSeparator)
	{
		$this->titleSeparator = $titleSeparator;
	}


	/**
	 * @return string
	 */
	public function getTitleSeparator()
	{
		return $this->titleSeparator;
	}


	/**
	 * @param string $titleTemplate
	 */
	public function setTitleTemplate($titleTemplate)
	{
		$this->titleTemplate = $titleTemplate;
	}


	/**
	 * @return string
	 */
	public function getTitleTemplate()
	{
		return $this->titleTemplate;
	}


	/**
	 * @param $link
	 * @param $title
	 */
	public function addFeed($link, $title)
	{
		$this->feeds[$link] = $title;
	}


	/**
	 * @return array
	 */
	public function getFeeds()
	{
		if ($this->presenter instanceof PagePresenter) {
			$repository = $this->presenter->getEntityManager()->getRepository('CmsModule\Pages\Rss\RssEntity');
			foreach ($repository->findAll() as $tag) {
				$this->addFeed($this->presenter->link('Route', array('route' => $tag)), $tag->name);
			}
		}

		return $this->feeds;
	}

}
