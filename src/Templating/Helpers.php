<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Templating;

use Nette\DI\Container;

/**
 * @author     Josef Kříž
 */
class Helpers extends \Nette\Object
{

	/** @var \SystemContainer|Container */
	private $container;

	/** @var \SystemContainer|Container */
	private $helpers = array();


	/**
	 * @param Container $container
	 */
	public function __construct(Container $container)
	{
		$this->container = $container;
	}


	/**
	 * @param $name
	 * @param $factory
	 */
	public function addHelper($name, $factory)
	{
		$this->helpers[$name] = $factory;
	}


	/**
	 * @param $helper
	 * @return \Nette\Callback
	 */
	public function loader($helper)
	{
		if (isset($this->helpers[$helper])) {
			return callback($this->helpers[$helper], 'filter');
		}
	}
}
