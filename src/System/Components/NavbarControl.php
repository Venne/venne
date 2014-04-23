<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\System\Components;

use Venne\System\UI\Control;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class NavbarControl extends Control
{

	/**
	 * @param $name
	 * @param $label
	 * @param null $icon
	 * @return Section
	 */
	public function addSection($name, $label, $icon = NULL)
	{
		return $this[$name] = new Section($label, $icon);
	}


	/**
	 * @param $name
	 * @return Section
	 */
	public function getSection($name)
	{
		return $this[$name];
	}


	/**
	 * Returns navbar.
	 * @param  bool   throw exception if form doesn't exist?
	 * @return NavbarControl
	 */
	public function getNavbar($need = TRUE)
	{
		return $this->lookup('Venne\System\Components\NavbarControl', $need);
	}


	public function render()
	{
		$this->template->render();
	}


	public function handleClick($id)
	{
		$id = explode('-', $id, 2);
		$section = $this[$id[1]];
		$section->onClick($section);
	}
}
