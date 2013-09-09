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

use Nette\Utils\Html;

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

	/** @var Html */
	protected $control;


	/**
	 * @param string $label
	 */
	public function __construct($label)
	{
		parent::__construct();

		$this->label = $label;
		$this->control = Html::el('a');
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


	public function getControl($id)
	{
		$control = clone $this->control;
		$control->class[] = 'ajax btn btn-default btn-sm';
		$control->disabled = $this->disabled;

		if ($this->getOption('data-confirm')) {
			$control->{'data-confirm'} = $this->getOption('data-confirm');
		}

		$control->setText($this->parent->template->translate($this->getLabel()));

		$control->href = $this->parent->link('doAction', array('name' => $this->name, 'id' => $id));

		return $control;
	}


	/**
	 * Returns control's HTML element template.
	 * @return Nette\Utils\Html
	 */
	final public function getControlPrototype()
	{
		return $this->control;
	}
}
