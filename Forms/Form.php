<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace CmsModule\Forms;

use Venne;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class Form extends \DoctrineModule\Forms\Form
{

	/**
	 * Adds upload input for FileEntity.
	 *
	 * @param $name
	 * @param null $label
	 * @return \DoctrineModule\Forms\Containers\EntityContainer
	 */
	public function addFileEntityInput($name, $label = NULL)
	{
		return $this[$name] = new \CmsModule\Forms\Controls\FileEntityControl($name, $label);
	}

}
