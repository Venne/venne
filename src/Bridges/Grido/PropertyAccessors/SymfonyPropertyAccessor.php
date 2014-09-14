<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Bridges\Grido\PropertyAccessors;

use Symfony\Component\PropertyAccess\PropertyAccessor;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class SymfonyPropertyAccessor
	extends \Nette\Object
	implements \Grido\PropertyAccessors\IPropertyAccessor
{

	/** @var \Symfony\Component\PropertyAccess\PropertyAccessor */
	private static $propertyAccessor;

	/**
	 * @param mixed $object
	 * @param string $name
	 * @return mixed
	 */
	public static function getProperty($object, $name)
	{
		return self::getPropertyAccessor()->getValue($object, self::formatName($name));
	}

	/**
	 * @param mixed $object
	 * @param string $name
	 * @param string $value
	 * @return void
	 */
	public static function setProperty($object, $name, $value)
	{
		self::getPropertyAccessor()->setValue($object, self::formatName($name), $value);
	}

	/**
	 * @return \Symfony\Component\PropertyAccess\PropertyAccessor
	 */
	private static function getPropertyAccessor()
	{
		if (self::$propertyAccessor === null) {
			self::$propertyAccessor = new PropertyAccessor(true);
		}

		return self::$propertyAccessor;
	}

	/**
	 * @param string $name
	 * @return string
	 */
	private static function formatName($name)
	{
		return str_replace('__', '.', $name);
	}

}
