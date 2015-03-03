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

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class Object
{

	/**
	 * @param string $name
	 * @param mixed[] $arguments
	 * @return mixed
	 * @internal
	 */
	public function __call($name, $arguments)
	{
		return ObjectMixin::call($this, $name, $arguments);
	}

	/**
	 * @param string $name
	 * @param mixed[] $arguments
	 * @return mixed
	 * @internal
	 */
	public static function __callStatic($name, $arguments)
	{
		return ObjectMixin::call(get_called_class(), $name, $arguments);
	}

	/**
	 * @param string $name
	 * @return mixed
	 * @internal
	 */
	public function &__get($name)
	{
		return ObjectMixin::get($this, $name);
	}

	/**
	 * @param string $name
	 * @param mixed $value
	 * @internal
	 */
	public function __set($name, $value)
	{
		ObjectMixin::set($this, $name, $value);
	}

	/**
	 * @param string $name
	 * @return mixed
	 * @internal
	 */
	public function __isset($name)
	{
		return ObjectMixin::has($this, $name);
	}

	/**
	 * @param string $name
	 * @internal
	 */
	public function __unset($name)
	{
		ObjectMixin::remove($this, $name);
	}

}
