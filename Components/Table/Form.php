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
class Form extends \Nette\ComponentModel\Component
{

	/** @var \Venne\Forms\FormFactory */
	protected $factory;

	/** @var string */
	protected $title;

	/** @var string */
	protected $width;

	/** @var string */
	protected $height;


	/**
	 * @param \Venne\Forms\FormFactory $factory
	 * @param string $title
	 * @param null $width
	 * @param null $height
	 */
	public function __construct(\Venne\Forms\FormFactory $factory, $title, $width = NULL, $height = NULL)
	{
		parent::__construct();

		$this->factory = $factory;
		$this->title = $title;
		$this->width = $width;
		$this->height = $height;
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
	 * @param \Venne\Forms\FormFactory $factory
	 */
	public function setFactory($factory)
	{
		$this->factory = $factory;
	}


	/**
	 * @return \Venne\Forms\FormFactory
	 */
	public function getFactory()
	{
		return $this->factory;
	}


	/**
	 * @param $height
	 */
	public function setHeight($height)
	{
		$this->height = $height;
	}


	/**
	 * @return null
	 */
	public function getHeight()
	{
		return $this->height;
	}


	/**
	 * @param $width
	 */
	public function setWidth($width)
	{
		$this->width = $width;
	}


	/**
	 * @return null
	 */
	public function getWidth()
	{
		return $this->width;
	}


	/**
	 * @param string $title
	 */
	public function setTitle($title)
	{
		$this->title = $title;
	}


	/**
	 * @return string
	 */
	public function getTitle()
	{
		return $this->title;
	}
}
