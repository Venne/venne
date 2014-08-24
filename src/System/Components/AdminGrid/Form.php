<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\System\Components\AdminGrid;

use Nette\ComponentModel\Component;
use Venne\Forms\IFormFactory;
use Venne\System\Components\Table\TableControl;
use Venne\System\UI\IDoctrineFormFactory;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class Form extends Component
{

	const TYPE_NORMAL = '';

	const TYPE_LARGE = 'modal-large';

	const TYPE_XLARGE = 'modal-xlarge';

	const TYPE_XXLARGE = 'modal-xxlarge';

	const TYPE_FULL = 'modal-full';

	/** @var array */
	public $onCreate;

	/** @var \Venne\Forms\IFormFactory */
	private $factory;

	/** @var callable */
	private $entityFactory;

	/** @var string */
	private $title;

	/** @var string */
	private $type;

	/**
	 * @param \Venne\Forms\IFormFactory $factory
	 * @param string $title
	 * @param callable $entityFactory
	 * @param null $type
	 */
	public function __construct(IFormFactory $factory, $title, $entityFactory = null, $type = null)
	{
		parent::__construct();

		$this->factory = $factory;
		$this->title = $title;
		$this->entityFactory = $entityFactory;
		$this->type = $type;
	}

	/**
	 * Returns table.
	 *
	 * @param  bool   throw exception if form doesn't exist?
	 * @return TableControl
	 */
	public function getTable($need = true)
	{
		return $this->lookup('Venne\System\Components\Table\TableControl', $need);
	}

	/**
	 * @param \Venne\Forms\IFormFactory $factory
	 */
	public function setFactory(IFormFactory $factory)
	{
		$this->factory = $factory;
	}

	/**
	 * @return IDoctrineFormFactory
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
