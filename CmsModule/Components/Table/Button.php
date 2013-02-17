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
use Venne\Application\UI\Control;
use Nette\ComponentModel\IContainer;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class Button extends \Nette\ComponentModel\Component
{

	/** @var array */
	public $onClick;

	/** @var array */
	public $onSuccess;

	/** @var array */
	public $onRender;

	/** @var string */
	protected $label;

	/** @var array */
	protected $options = array();

	/** @var bool */
	protected $disabled = FALSE;


	/**
	 * @param string $label
	 */
	public function __construct($label)
	{
		parent::__construct();

		$this->label = $label;
	}


	/**
	 * @param $label
	 * @return $this
	 */
	public function setLabel($label)
	{
		$this->label = $label;
		return $this;
	}


	/**
	 * @return string
	 */
	public function getLabel()
	{
		return $this->label;
	}


	/**
	 * Returns table.
	 * @param  bool   throw exception if form doesn't exist?
	 * @return \CmsModule\Components\Table\TableControl
	 */
	public function getTable($need = TRUE)
	{
		return $this->lookup('CmsModule\Components\Table\TableControl', $need);
	}


	/**
	 * @param $key
	 * @param $value
	 * @return $this
	 */
	public function setOptions($key, $value)
	{
		$this->options[$key] = $value;
		return $this;
	}


	/**
	 * @param $key
	 * @return null
	 */
	public function getOption($key)
	{
		return isset($this->options[$key]) ? $this->options[$key] : NULL;
	}


	/**
	 * @param bool $value
	 * @return $this
	 */
	public function setDisabled($value = TRUE)
	{
		$this->disabled = (bool)$value;
		return $this;
	}


	/**
	 * @return bool
	 */
	public function isDisabled()
	{
		return $this->disabled;
	}
}
