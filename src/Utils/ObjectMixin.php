<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Utils;

use Nette\MemberAccessException;
use Nette\StaticClassException;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class ObjectMixin
{

	final public function __construct()
	{
		throw new StaticClassException;
	}

	/**
	 * @param mixed $_this
	 * @param string $name
	 */
	public static function call($_this, $name)
	{
		$class = get_class($_this);

		if (method_exists($class, $name)) { // called parent::$name()
			$class = 'parent';
		}
		throw new MemberAccessException("Call to undefined method $class::$name().");
	}

	/**
	 * @param mixed $class
	 * @param string $method
	 */
	public static function callStatic($class, $method)
	{
		throw new MemberAccessException("Call to undefined static method $class::$method().");
	}

	/**
	 * @param mixed $_this
	 * @param string $name
	 */
	public static function get($_this, $name)
	{
		$class = get_class($_this);

		$type = isset($methods['set' . ucfirst($name)]) ? 'a write-only' : 'an undeclared';
		throw new MemberAccessException("Cannot read $type property $class::\$$name.");
	}

	/**
	 * @param mixed $_this
	 * @param string $name
	 */
	public static function set($_this, $name)
	{
		$class = get_class($_this);
		$uname = ucfirst($name);

		$type = isset($methods['get' . $uname]) || isset($methods['is' . $uname])
			? 'a read-only' : 'an undeclared';
		throw new MemberAccessException("Cannot write to $type property $class::\$$name.");
	}

	/**
	 * @param mixed $class
	 * @param string $name
	 */
	public static function has($class, $name)
	{
		$class = get_class($class);
		throw new MemberAccessException("Cannot unset the property $class::\$$name.");
	}

	/**
	 * @param mixed $_this
	 * @param string $name
	 */
	public static function remove($_this, $name)
	{
		$class = get_class($_this);

		throw new MemberAccessException("Cannot unset the property $class::\$$name.");
	}

}
