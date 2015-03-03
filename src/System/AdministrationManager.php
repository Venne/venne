<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\System;

use Venne\Widgets\WidgetManagerFactory;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 *
 * @property-read string $routePrefix
 * @property-read string $defaultPresenter
 */
class AdministrationManager extends \Nette\Object
{

	/** @var string[] */
	private $administrationPages = array();

	/** @var string[] */
	private $sideComponents = array();

	/** @var string */
	private $routePrefix;

	/** @var string */
	private $defaultPresenter;

	/** @var string */
	private $theme;

	/** @var \Venne\Widgets\WidgetManagerFactory */
	private $widgetManagerFactory;

	/** @var string[] */
	private $jsFiles = array();

	/** @var string[] */
	private $cssFiles = array();

	/**
	 * @param string $routePrefix
	 * @param string $defaultPresenter
	 * @param string $theme
	 * @param \Venne\Widgets\WidgetManagerFactory $widgetManagerFactory
	 */
	public function __construct(
		$routePrefix,
		$defaultPresenter,
		$theme,
		WidgetManagerFactory $widgetManagerFactory
	) {
		$this->routePrefix = $routePrefix;
		$this->defaultPresenter = $defaultPresenter;
		$this->theme = $theme;
		$this->widgetManagerFactory = $widgetManagerFactory;
	}

	/**
	 * @param string $file
	 * @param bool $args
	 * @return $this
	 */
	public function addCssFile($file, $args = true)
	{
		$this->cssFiles[trim($file)] = $args;

		return $this;
	}

	/**
	 * @return string[]
	 */
	public function getCssFiles()
	{
		return $this->cssFiles;
	}

	/**
	 * @param string $file
	 * @param bool $args
	 * @return $this
	 */
	public function addJsFile($file, $args = true)
	{
		$this->jsFiles[trim($file)] = $args;

		return $this;
	}

	/**
	 * @return string[]
	 */
	public function getJsFiles()
	{
		return $this->jsFiles;
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
	 * @param string $link
	 * @param string $name
	 * @param string $description
	 * @param string|null $category
	 * @return $this
	 */
	public function addAdministrationPage($link, $name, $description, $category = null)
	{
		if ($category) {
			$pages = &$this->administrationPages[$category];
		} else {
			$pages = &$this->administrationPages;
		}
		$pages[] = array(
			'link' => $link,
			'name' => $name,
			'description' => $description,
			'category' => $category,
		);

		return $this;
	}

	/**
	 * Get Administration pages as array
	 *
	 * @return string[]
	 */
	public function getAdministrationPages()
	{
		return $this->administrationPages;
	}

	/**
	 * @param string $name
	 * @param string $description
	 * @param callable $factory
	 * @param string[] $args
	 * @return $this
	 */
	public function addSideComponent($name, $description, $factory, array $args = array())
	{
		$this->sideComponents[$name] = array(
			'name' => $name,
			'description' => $description,
			'factory' => $factory,
			'args' => $args,
		);

		return $this;
	}

	/**
	 * @return mixed[][]
	 */
	public function getSideComponents()
	{
		return $this->sideComponents;
	}

	/**
	 * @return \Venne\Widgets\WidgetManager
	 */
	public function getTrayWidgetManager()
	{
		static $trayWidgetManager;

		if ($trayWidgetManager === null) {
			$trayWidgetManager = $this->widgetManagerFactory->create();
		}

		return $trayWidgetManager;
	}

}
