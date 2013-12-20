<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace CmsModule\Content;

use Nette\Object;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 *
 * @property-read string $author
 * @property-read string $defaultLanguage
 * @property-read string $defaultPresenter
 * @property-read string $description
 * @property-read string $errorPresenter
 * @property-read string $keywords
 * @property-read string $languages
 * @property-read string $name
 * @property-read string $oneWayRoutePrefix
 * @property-read string $routePrefix
 * @property-read string $theme
 * @property-read string $title
 * @property-read string $titleSeparator
 */
class WebsiteManager extends Object
{

	/** @var string */
	private $name;

	/** @var string */
	private $title;

	/** @var string */
	private $titleSeparator;

	/** @var string */
	private $keywords;

	/** @var string */
	private $description;

	/** @var string */
	private $author;

	/** @var string */
	private $routePrefix;

	/** @var string */
	private $oneWayRoutePrefix;

	/** @var string */
	private $theme;

	/** @var string */
	private $languages;

	/** @var string */
	private $defaultLanguage;

	/** @var string */
	private $defaultPresenter;

	/** @var string */
	private $errorPresenter;


	/**
	 * @param $author
	 * @param $defaultLanguage
	 * @param $defaultPresenter
	 * @param $description
	 * @param $errorPresenter
	 * @param $keywords
	 * @param $languages
	 * @param $name
	 * @param $oneWayRoutePrefix
	 * @param $routePrefix
	 * @param $theme
	 * @param $title
	 * @param $titleSeparator
	 */
	public function __construct($author, $defaultLanguage, $defaultPresenter, $description, $errorPresenter, $keywords, $languages, $name, $oneWayRoutePrefix, $routePrefix, $theme, $title, $titleSeparator)
	{
		$this->author = $author;
		$this->defaultLanguage = $defaultLanguage;
		$this->defaultPresenter = $defaultPresenter;
		$this->description = $description;
		$this->errorPresenter = $errorPresenter;
		$this->keywords = $keywords;
		$this->languages = $languages;
		$this->name = $name;
		$this->oneWayRoutePrefix = $oneWayRoutePrefix;
		$this->routePrefix = $routePrefix;
		$this->theme = $theme;
		$this->title = $title;
		$this->titleSeparator = $titleSeparator;
	}


	/**
	 * @return string
	 */
	public function getAuthor()
	{
		return $this->author;
	}


	/**
	 * @return string
	 */
	public function getDefaultLanguage()
	{
		return $this->defaultLanguage;
	}


	/**
	 * @return string
	 */
	public function getDefaultPresenter()
	{
		return $this->defaultPresenter;
	}


	/**
	 * @return string
	 */
	public function getDescription()
	{
		return $this->description;
	}


	/**
	 * @return string
	 */
	public function getErrorPresenter()
	{
		return $this->errorPresenter;
	}


	/**
	 * @return string
	 */
	public function getKeywords()
	{
		return $this->keywords;
	}


	/**
	 * @return string
	 */
	public function getLanguages()
	{
		return $this->languages;
	}


	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}


	/**
	 * @return string
	 */
	public function getOneWayRoutePrefix()
	{
		return $this->oneWayRoutePrefix;
	}


	/**
	 * @return string
	 */
	public function getRoutePrefix()
	{
		return $this->routePrefix;
	}


	/**
	 * @return string
	 */
	public function getTheme()
	{
		return $this->theme;
	}


	/**
	 * @return string
	 */
	public function getTitle()
	{
		return $this->title;
	}


	/**
	 * @return string
	 */
	public function getTitleSeparator()
	{
		return $this->titleSeparator;
	}

}

