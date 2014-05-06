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

use Nette\Object;
use Venne\Widgets\IWidgetManagerFactory;
use Venne\Widgets\WidgetManager;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 *
 * @property-read string $routePrefix
 * @property-read string $defaultPresenter
 * @property-read array $login
 */
class AdministrationManager extends Object
{

	/** @var array */
	private $administrationPages = array();

	/** @var array */
	private $sideComponents = array();

	/** @var string */
	private $routePrefix;

	/** @var string */
	private $defaultPresenter;

	/** @var array */
	private $login;

	/** @var string */
	private $theme;

	/** @var WidgetManager */
	private $trayWidgetManager;

	/** @var IWidgetManagerFactory */
	private $widgetManagerFactory;


	/**
	 * @param $routePrefix
	 * @param $defaultPresenter
	 * @param $login
	 * @param $theme
	 * @param IWidgetManagerFactory $widgetManagerFactory
	 */
	public function __construct($routePrefix, $defaultPresenter, $login, $theme, IWidgetManagerFactory $widgetManagerFactory)
	{
		$this->routePrefix = $routePrefix;
		$this->defaultPresenter = $defaultPresenter;
		$this->login = $login;
		$this->theme = $theme;
		$this->widgetManagerFactory = $widgetManagerFactory;
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
	 * @return array
	 */
	public function getLogin()
	{
		return $this->login;
	}


	/**
	 * @return string
	 */
	public function getTheme()
	{
		return $this->theme;
	}


	/**
	 * @param $link
	 * @param $name
	 * @param $description
	 * @param null $category
	 * @return $this
	 */
	public function addAdministrationPage($link, $name, $description, $category = NULL)
	{
		if ($category) {
			$pages = & $this->administrationPages[$category];
		} else {
			$pages = & $this->administrationPages;
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
	 * @return array
	 */
	public function getAdministrationPages()
	{
		return $this->administrationPages;
	}


	/**
	 * @param $name
	 * @param $description
	 * @param $factory
	 * @param null $icon
	 * @return $this
	 */
	public function addSideComponent($name, $description, $factory, $icon = NULL)
	{
		$this->sideComponents[$name] = array(
			'name' => $name,
			'description' => $description,
			'factory' => $factory,
			'icon' => $icon,
		);
		return $this;
	}


	/**
	 * @return array
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
		if (!$this->trayWidgetManager) {
			$this->trayWidgetManager = $this->widgetManagerFactory->create();
		}

		return $this->trayWidgetManager;
	}

}
