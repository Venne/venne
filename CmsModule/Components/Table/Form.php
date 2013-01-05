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
use Nette\ComponentModel\IContainer;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class Form extends \Nette\ComponentModel\Component
{

	const TYPE_NORMAL = '';

	const TYPE_LARGE = 'modal-large';

	const TYPE_XLARGE = 'modal-xlarge';

	const TYPE_XXLARGE = 'modal-xxlarge';

	const TYPE_FULL = 'modal-full';

	/** @var \Venne\Forms\FormFactory */
	protected $factory;

	/** @var callable */
	protected $entityFactory;

	/** @var string */
	protected $title;

	/** @var string */
	protected $type;


	/**
	 * @param \Venne\Forms\FormFactory $factory
	 * @param string $title
	 * @param callable $entityFactory
	 * @param null $type
	 */
	public function __construct(\Venne\Forms\FormFactory $factory, $title, $entityFactory = NULL, $type = NULL)
	{
		parent::__construct();

		$this->factory = $factory;
		$this->title = $title;
		$this->entityFactory = $entityFactory;
		$this->type = $type;
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
	 * @param string $type
	 */
	public function setType($type)
	{
		$this->type = $type;
	}


	/**
	 * @return string
	 */
	public function getType()
	{
		return $this->type;
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


	/**
	 * @param callable $entityFactory
	 */
	public function setEntityFactory($entityFactory)
	{
		$this->entityFactory = $entityFactory;
	}


	/**
	 * @return callable
	 */
	public function getEntityFactory()
	{
		return $this->entityFactory;
	}
}
