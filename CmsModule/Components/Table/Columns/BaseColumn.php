<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace CmsModule\Components\Table\Columns;

use Venne;
use CmsModule\Components\Table\IColumn;
use CmsModule\Components\Table\Filters\BaseFilter;
use CmsModule\Components\Table\TableControl;
use Nette\ComponentModel\Component;
use Nette\ComponentModel\IContainer;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class BaseColumn extends Component implements IColumn
{

	const FILTER_TEXT = 'CmsModule\Components\Table\Filters\BaseFilter';

	/** @var TableControl */
	protected $table;

	/** @var string */
	protected $title;

	/** @var string */
	protected $width;

	/** @var NULL|callable */
	protected $callback;

	/** @var */
	protected $filter;

	/** @var bool */
	protected $sortable = FALSE;


	/**
	 * @param TableControl $table
	 * @param string $title
	 * @param null $width
	 * @param null $callback
	 */
	public function __construct(TableControl $table, $name, $title)
	{
		parent::__construct($table, $name);

		$this->table = $table;
		$this->title = $title;
	}


	/**
	 * @param $title
	 * @return $this
	 */
	public function setTitle($title)
	{
		$this->title = $title;
		return $this;
	}


	/**
	 * @return string
	 */
	public function getTitle()
	{
		return $this->title;
	}


	/**
	 * @param string $width
	 * @return $this
	 */
	public function setWidth($width)
	{
		$this->width = $width;
		return $this;
	}


	/**
	 * @return string
	 */
	public function getWidth()
	{
		return $this->width;
	}


	/**
	 * @param callable|NULL $callback
	 * @return $this
	 */
	public function setCallback($callback)
	{
		$this->callback = $callback;
		return $this;
	}


	/**
	 * @return callable|NULL
	 */
	public function getCallback()
	{
		return $this->callback;
	}


	/**
	 * @param bool $sortable
	 * @return $this
	 */
	public function setSortable($sortable = TRUE)
	{
		$this->sortable = (bool)$sortable;
		return $this;
	}


	/**
	 * @return bool
	 */
	public function isSortable()
	{
		return $this->sortable;
	}


	/**
	 * @param string $type
	 * @return IFilter
	 */
	public function setFilter($type = self::FILTER_TEXT)
	{
		$type = '\\' . trim($type, '\\');
		return $this->filter = $type ? new $type($this) : NULL;
	}


	/**
	 * @return BaseFilter|null
	 */
	public function getFilter()
	{
		return $this->filter;
	}
}
