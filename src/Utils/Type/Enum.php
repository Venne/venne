<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Utils\Type;

use Nette\InvalidArgumentException;
use Nette\Reflection\ClassType;
use Nette\StaticClassException;
use Venne\Utils\Object;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
abstract class Enum extends Object
{

	/** @var mixed[] */
	protected static $availableValues = array();

	/** @var \Venne\Utils\Type\Enum[] */
	private static $instances = array();

	/** @var mixed */
	private $value;

	/** @var bool */
	private static $disableConstructor = true;

	/**
	 * @param mixed $value
	 */
	public function __construct($value)
	{
		if (self::$disableConstructor) {
			throw new StaticClassException;
		}

		$this->setValue($value);
	}

	/**
	 * @return mixed[]
	 */
	public static function getAvailableValues()
	{
		$class = get_called_class();

		if (!isset(static::$availableValues[$class])) {
			$classReflection = new ClassType($class);
			static::$availableValues[$class] = array_values($classReflection->getConstants());
		}

		return static::$availableValues[$class];
	}

	/**
	 * @param $value
	 * @return static
	 */
	public static function get($value)
	{
		static::checkValue($value);
		$key = static::getKeyByValue($value);

		if (!isset(self::$instances[$key])) {
			self::$disableConstructor = false;
			self::$instances[$key] = new static($value);
			self::$disableConstructor = true;
		}

		return self::$instances[$key];
	}

	/**
	 * @return mixed
	 */
	public function getValue()
	{
		return $this->value;
	}

	/**
	 * @param \Venne\Utils\Type\Enum $enum
	 * @return bool
	 */
	public function equals(self $enum)
	{
		return get_class($this) === get_class($enum) && $this->getValue() === $enum->getValue();
	}

	/**
	 * @param mixed $value
	 * @return bool
	 */
	public function equalsValue($value)
	{
		return $this->getValue() === $value;
	}

	/**
	 * @param mixed|mixed[] $values
	 */
	protected static function checkValue($values)
	{
		foreach ((array) $values as $value) {
			if (!in_array($value, static::getAvailableValues(), true)) {
				throw new InvalidArgumentException(sprintf('\'%s\' [%s] is not valid argument. Accepted values are: %s', $value, gettype($value), implode(', ', static::getAvailableValues())));
			}
		}
	}

	/**
	 * @param mixed $value
	 * @return string
	 */
	protected static function getKeyByValue(& $value)
	{
		return sprintf('%s::%s', get_called_class(), $value);
	}

	/**
	 * @param mixed $value
	 */
	protected function setValue($value)
	{
		$this->value = $value;
	}

	/**
	 * @return string
	 */
	public function __toString()
	{
		return (string) $this->getValue();
	}

}
