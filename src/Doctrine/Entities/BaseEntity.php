<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Doctrine\Entities;

use Kdyby\Doctrine\Entities\SerializableMixin;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class BaseEntity extends \Venne\Utils\Object implements \Serializable
{

	/** @var bool[][] */
	private static $synchronizer = array();

	public function __construct()
	{
	}

	public function doTransaction(callable $callback, $hash = '_no_name')
	{
		$class = get_class($this);

		if (isset(self::$synchronizer[$class][$hash])) {
			return;
		}

		self::$synchronizer[$class][$hash] = true;
		$callback();
		unset(self::$synchronizer[$class][$hash]);
	}

	/**
	 * @internal
	 * @return string
	 */
	public function serialize()
	{
		return SerializableMixin::serialize($this);
	}

	/**
	 * @internal
	 * @param string $serialized
	 */
	public function unserialize($serialized)
	{
		SerializableMixin::unserialize($this, $serialized);
	}

}
