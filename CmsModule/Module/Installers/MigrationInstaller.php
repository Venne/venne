<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace CmsModule\Module\Installers;

use Nette\DI\Container;
use Nette\Object;
use Venne\Module\IInstaller;
use Venne\Module\IModule;


/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class MigrationInstaller extends Object implements IInstaller
{

	/** @var Container|\SystemContainer */
	protected $context;


	/**
	 * @param Container $context
	 */
	public function __construct(Container $context)
	{
		$this->context = $context;
	}


	/**
	 * @param IModule $module
	 */
	public function install(IModule $module)
	{
	}


	/**
	 * @param IModule $module
	 */
	public function uninstall(IModule $module)
	{
	}


	/**
	 * @param IModule $module
	 * @param $from
	 * @param $to
	 */
	public function upgrade(IModule $module, $from, $to)
	{
	}


	/**
	 * @param IModule $module
	 * @param $from
	 * @param $to
	 */
	public function downgrade(IModule $module, $from, $to)
	{
	}
}

