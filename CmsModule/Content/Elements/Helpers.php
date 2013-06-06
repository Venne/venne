<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace CmsModule\Content\Elements;

use Nette\Object;
use Nette\Utils\Strings;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class Helpers extends Object
{

	/**
	 * Encode element name to component name.
	 *
	 * @param $name
	 * @return mixed
	 */
	public static function encodeName($name)
	{
		$name = Strings::webalize($name, '_');
		return str_replace('-', '_', $name);
	}
}
