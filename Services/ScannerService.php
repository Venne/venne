<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace CmsModule\Services;

use Venne;
use Nette\Object;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 *
 * @property \SystemContainer|\Nette\DI\Container $context
 */
class scannerService extends Object
{


	/** @var \Nette\DI\Container|\SystemContainer */
	protected $context;



	public function __construct(\Nette\DI\Container $context)
	{
		$this->context = $context;
	}



	/**
	 * @return \Nette\Loaders\RobotLoader;
	 */
	protected function getRobotLoader()
	{
		return $this->context->robotLoader;
	}



	public function getModules()
	{
		$arr = array();
		foreach ($this->searchClassesBySubclass("Venne\Module\IModule", NULL, array("Venne\Module\Module", "CmsModule\Module\Module")) as $class) {
			$module = new $class;
			$arr[$module->getName()] = $module;
		}

		return $arr;
	}



	public function getLayoutFiles()
	{
		$data = array();
		foreach ($this->context->parameters['modules'] as $module => $item) {
			$path = $item['path'] . "/layouts";
			if (file_exists($path)) {
				foreach (\Nette\Utils\Finder::findDirectories("*")->in($path) as $file) {
					if (!isset($data[$module])) {
						$data[$module] = array();
					}
					$name = $file->getBasename();
					$data[$module]["@" . $module . "/" . $name] = "$name ($module)";
				}
			}
		}
		return $data;
	}



	/**
	 *
	 * @param string $subclass
	 * @param array $ignore
	 */
	public function searchClassesBySubclass($subclass, $prefix = "", $ignore = array())
	{
		$classes = array();
		$ignore = (array) $ignore;
		$robotLoader = $this->context->robotLoader;
		foreach ($robotLoader->getIndexedClasses() as $key => $item) {
			if (strpos($key, "Test") !== false || strpos($key, "/Testing/") !== false) {
				continue;
			}
			if (in_array($key, $ignore)) {
				continue;
			}
			if ($prefix && strpos($key, $prefix) !== 0) {
				continue;
			}
			$class = "\\{$key}";

			$classReflection = new \Nette\Reflection\ClassType($class);
			try {
				if ($classReflection->isSubclassOf($subclass)) {
					$classes[] = $key;
				}
			} catch (\Exception $e) {
				
			}
		}
		return $classes;
	}

}

