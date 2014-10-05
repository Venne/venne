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

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class NavbarControl extends \Venne\System\UI\Control
{

	/**
	 * @param string $name
	 * @param string $label
	 * @param string|null $icon
	 * @return \Venne\System\Components\Section
	 */
	public function addSection($name, $label, $icon = null)
	{
		return $this[$name] = new Section($label, $icon);
	}

	/**
	 * @param string $name
	 * @return \Venne\System\Components\Section
	 */
	public function getSection($name)
	{
		return $this[$name];
	}

	/**
	 * Returns navbar.
	 *
	 * @param bool
	 * @return \Venne\System\Components\NavbarControl
	 */
	public function getNavbar($need = true)
	{
		return $this->lookup('Venne\System\Components\NavbarControl', $need);
	}

	public function render()
	{
		$this->template->render();
	}

	/**
	 * @param int $id
	 */
	public function handleClick($id)
	{
		$id = explode('-', $id, 2);
		$section = $this[$id[1]];
		$section->onClick($section);
	}

}
