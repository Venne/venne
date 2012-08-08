<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace CmsModule\Services;

use Venne;
use Nette\Object;
use Nette\Config\Adapters\NeonAdapter;
use Nette\OutOfRangeException;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class ConfigBuilder extends Object implements \ArrayAccess, \Countable, \IteratorAggregate
{


	/** @var array */
	protected $data;

	/** @var array */
	protected $dataOrig;

	/** @var array */
	protected $sections;

	/** @var string */
	protected $fileName;

	/** @var Nette\Config\Adapters\NeonAdapter */
	protected $adapter;



	/**
	 * @param string $fileName
	 */
	public function __construct($fileName)
	{
		$this->fileName = $fileName;
		$this->adapter = new NeonAdapter;
		$this->load();
	}



	/**
	 * Load data
	 */
	public function load()
	{
		$this->data = \Nette\ArrayHash::from($this->adapter->load($this->fileName), true);
	}



	/**
	 * Save data
	 */
	public function save()
	{
		file_put_contents($this->fileName, $this->adapter->dump((array)$this->data));
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
	 * @param  mixed
	 * @return bool
	 */
	public function offsetExists($index)
	{
		return $index >= 0 && $index < count($this->data);
	}



	/**
	 * Returns a item.
	 *
	 * @param  mixed
	 * @return mixed
	 */
	public function offsetGet($index)
	{
		if ($index < 0 || $index >= count($this->data)) {
			throw new OutOfRangeException("Offset invalid or out of range");
		}
		return $this->data[$index];
	}



	/**
	 * Replaces or appends a item.
	 *
	 * @param  mixed
	 * @param  mixed
	 * @return void
	 */
	public function offsetSet($index, $value)
	{
		if ($index === NULL) {
			$this->data[] = is_array($value) ? \Nette\ArrayHash::from($value, true) : $value;
		} else {
			$this->data[$index] = is_array($value) ? \Nette\ArrayHash::from($value, true) : $value;
		}
	}



	/**
	 * Removes the element from this list.
	 *
	 * @param  mixed
	 * @return void
	 */
	public function offsetUnset($index)
	{
		if ($index < 0 || $index >= count($this->data)) {
			throw new OutOfRangeException("Offset invalid or out of range");
		}
		array_splice($this->data, $index, 1);
	}

}

