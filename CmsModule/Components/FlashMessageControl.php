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

use Venne;
use Nette\ComponentModel\Component;
use CmsModule\Content\Control;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class FlashMessageControl extends Control
{

	public function render($global = FALSE)
	{
		$this->template->flashes = $this->getFlashes($global);
		$this->template->render();
	}


	protected function getFlashes($global = FALSE)
	{
		$component = $this->presenter;

		$ret = $this->getFlashesByControl($component);

		if ($global) {
			foreach ($component->getComponents(TRUE) as $component) {
				$ret = array_merge($ret, (array)$this->getFlashesByControl($component));
			}
		}

		return $ret;
	}


	protected function getFlashesByControl(Component $component)
	{
		if ($component instanceof \Nette\Application\UI\Control) {
			$id = $component->getParameterId('flash');
			return (array)$component->presenter->getFlashSession()->$id;
		}
	}
}
