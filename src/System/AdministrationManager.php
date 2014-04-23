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

use Nette\DI\Container;
use Nette\Object;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 *
 * @property-read string $routePrefix
 * @property-read string $defaultPresenter
 * @property-read array $login
 */
class AdministrationManager extends Object
{

	/** @var Container */
	private $context;

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


	/**
	 * @param $routePrefix
	 * @param $defaultPresenter
	 * @param $login
	 * @param $theme
	 * @param Container $context
	 */
	public function __construct($routePrefix, $defaultPresenter, $login, $theme, Container $context)
	{
		$this->routePrefix = $routePrefix;
		$this->defaultPresenter = $defaultPresenter;
		$this->login = $login;
		$this->theme = $theme;
		$this->context = $context;
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


	public function addAdministrationPage($link, $name, $description, $category)
	{
		$this->administrationPages[$link] = array(
			'name' => $name,
			'description' => $description,
			'category' => $category,
		);
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
	 * Get Administration pages as array
	 *
	 * @return array
	 */
	public function getAdministrationNavigation()
	{
		$ret = array();

		foreach ($this->administrationPages as $link => $item) {
			$ret[$item['category']][$link] = $item;
		}

		return $ret;
	}


	/**
	 * @param $name
	 * @param $description
	 * @param $factory
	 * @param null $icon
	 */
	public function addSideComponent($name, $description, $factory, $icon = NULL)
	{
		$this->sideComponents[$name] = array(
			'name' => $name,
			'description' => $description,
			'factory' => $factory,
			'icon' => $icon,
		);
	}


	/**
	 * @return array
	 */
	public function getSideComponents()
	{
		return $this->sideComponents;
	}

}

