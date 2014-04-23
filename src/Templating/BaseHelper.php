<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Templating;

use Nette\Object;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class BaseHelper extends Object implements IHelper
{

	/** @var LatteHelper */
	protected static $instance;


	public function __construct()
	{
		self::$instance = $this;
	}


	/**
	 * @static
	 * @return string
	 */
	public static function filter()
	{
		return call_user_func_array(array(self::$instance, 'run'), func_get_args());
	}
}

