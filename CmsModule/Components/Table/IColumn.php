<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace CmsModule\Components\Table;

use Venne;
use CmsModule\Components\Table\TableControl;
use Nette\ComponentModel\Component;
use Nette\ComponentModel\IContainer;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
interface IColumn
{


	public function __construct(TableControl $table, $name, $title);


	/**
	 * @param $title
	 * @return $this
	 */
	public function setTitle($title);


	/**
	 * @return string
	 */
	public function getTitle();


	/**
	 * @param string $width
	 * @return $this
	 */
	public function setWidth($width);


	/**
	 * @return string
	 */
	public function getWidth();


	/**
	 * @param callable|NULL $callback
	 * @return $this
	 */
	public function setCallback($callback);


	/**
	 * @return callable|NULL
	 */
	public function getCallback();


	/**
	 * @param bool $sortable
	 * @return $this
	 */
	public function setSortable($sortable = TRUE);


	/**
	 * @return bool
	 */
	public function isSortable();


	/**
	 * @param string $type
	 * @return IFilter
	 */
	public function setFilter($type = NULL);


	/**
	 * @return IFilter|null
	 */
	public function getFilter();
}
