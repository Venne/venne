<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace CmsModule\Administration;

use CmsModule\Administration\StructureInstallators\IStructureInstallator;
use Nette\InvalidArgumentException;
use Nette\Object;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class StructureInstallatorManager extends Object
{

	/** @var IStructureInstallator[] */
	private $installators = array();


	/**
	 * @param IStructureInstallator $installator
	 * @param $name
	 * @throws InvalidArgumentException
	 */
	public function registerInstallator(IStructureInstallator $installator, $name)
	{
		if (isset($this->installators[$name])) {
			throw new InvalidArgumentException("Installator with name '{$name}' already exists.");
		}

		$this->installators[$name] = $installator;
	}


	/**
	 * @return IStructureInstallator[]
	 */
	public function getInstallators()
	{
		return $this->installators;
	}


	/**
	 * @param $name
	 * @return IStructureInstallator
	 * @throws InvalidArgumentException
	 */
	public function getInstallatorByName($name)
	{
		if (!isset($this->installators[$name])) {
			throw new InvalidArgumentException("Installator with name '{$name}' does not exist.");
		}

		return $this->installators[$name];
	}
}

