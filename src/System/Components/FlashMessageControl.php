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

use Nette\ComponentModel\Component;
use Venne\System\UI\Control;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class FlashMessageControl extends Control
{

	/**
	 * @param bool $global
	 */
	public function renderDefault($global = false)
	{
		$this->template->flashes = $this->getFlashes($global);
	}

	/**
	 * @param bool $global
	 * @return string[]
	 */
	protected function getFlashes($global = false)
	{
		$component = $this->parent;

		$ret = $this->getFlashesByControl($component);

		if ($global) {
			foreach ($component->getComponents(true) as $component) {
				$ret = array_merge($ret, (array) $this->getFlashesByControl($component));
			}
		}

		return $ret;
	}

	/**
	 * @param \Nette\ComponentModel\Component $component
	 * @return string[]
	 */
	protected function getFlashesByControl(Component $component)
	{
		if ($component instanceof \Nette\Application\UI\Control) {
			$id = $component->getParameterId('flash');

			return (array) $component->presenter->getFlashSession()->$id;
		}
	}

}
