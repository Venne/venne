<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Config;

use Nette\DI\Config\Adapters\NeonAdapter;
use Nette\OutOfRangeException;
use Nette\Utils\ArrayHash;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class ConfigBuilder extends \Nette\Object implements \ArrayAccess, \Countable, \IteratorAggregate
{

	/** @var mixed */
	private $data;

	/** @var string */
	private $fileName;

	/** @var \Nette\DI\Config\Adapters\NeonAdapter */
	private $adapter;

	/**
	 * @param string $fileName
	 */
	public function __construct($fileName)
	{
		$this->fileName = $fileName;
		$this->adapter = new NeonAdapter;
		$this->load();
	}

	public function load()
	{
		$this->data = ArrayHash::from($this->adapter->load($this->fileName), true);
	}

	public function save()
	{
		file_put_contents($this->fileName, $this->adapter->dump((array) $this->data));
		$this->load();
	}


	/* ------------------------------ Interfaces -------------------------------- */

	/**
	 * Returns items count.
	 *
	 * @return int
	 */
	public function count()
	{
		return $this->count($this->data);
	}

	/**
	 * Returns an iterator over all items.
	 *
	 * @return \RecursiveArrayIterator
	 */
	public function getIterator()
	{
		return new \ArrayIterator($this->data);
	}

	/**
	 * Determines whether a item exists.
	 *
	 * @param mixed
	 * @return bool
	 */
	public function offsetExists($index)
	{
		return $index >= 0 && $index < count($this->data);
	}

	/**
	 * Returns a item.
	 *
	 * @param mixed
	 * @return mixed
	 */
	public function offsetGet($index)
	{
		if ($index < 0 || $index >= count($this->data)) {
			throw new OutOfRangeException('Offset invalid or out of range');
		}

		return $this->data[$index];
	}

	/**
	 * Replaces or appends a item.
	 *
	 * @param mixed
	 * @param mixed
	 */
	public function offsetSet($index, $value)
	{
		if ($index === null) {
			$this->data[] = is_array($value) ? ArrayHash::from($value, true) : $value;
		} else {
			$this->data[$index] = is_array($value) ? ArrayHash::from($value, true) : $value;
		}
	}

	/**
	 * Removes the element from this list.
	 *
	 * @param mixed
	 */
	public function offsetUnset($index)
	{
		if ($index < 0 || $index >= count($this->data)) {
			throw new OutOfRangeException('Offset invalid or out of range');
		}
		array_splice($this->data, $index, 1);
	}

}
