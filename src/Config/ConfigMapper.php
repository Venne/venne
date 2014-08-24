<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Config;

use Nette\ComponentModel\IComponent;
use Nette\DI\Config\Adapters\NeonAdapter;
use Nette\Forms\Form;
use Nette\Utils\Arrays;
use Nette\Utils\Strings;
use Venne\Forms\Controls\EventControl;

/**
 * @author     Josef Kříž
 */
class ConfigMapper extends \Nette\Object
{

	/** @var string */
	protected $fileName;

	/** @var \Nette\DI\Config\Adapters\NeonAdapter */
	protected $adapter;

	/** @var string */
	protected $root;

	/** @var mixed */
	protected $data;

	/** @var \Nette\Forms\Container */
	protected $container;

	/**
	 * @param string $fileName
	 * @param string $root
	 */
	public function __construct($fileName, $root = '')
	{
		$this->fileName = $fileName;
		$this->setRoot($root);
		$this->adapter = new NeonAdapter;
	}

	/**
	 * @return string
	 */
	public function getRoot()
	{
		return implode('.', $this->root);
	}

	/**
	 * @param string $root
	 */
	public function setRoot($root)
	{
		$root = str_replace('\.', '\\', $root);
		$this->root = $root ? explode('.', $root) : array();
		foreach ($this->root as $key => $item) {
			$this->root[$key] = str_replace('\\', '.', $item);
		}
	}

	public function setForm(Form $container)
	{
		$this->container = $container;
		$this->container['_eventControl'] = $eventControl = new EventControl('_eventControl');
		$eventControl->onAttached[] = function () {
			$this->load();
			unset($this->container['_eventControl']);
		};
		$this->container->onSuccess[] = $this->saveConfig;
	}

	/**
	 * @param mixed $data
	 * @param \Nette\ComponentModel\IComponent $container
	 */
	public function assign($data, IComponent $container)
	{

	}

	/**
	 * @return mixed
	 */
	protected function loadConfig()
	{
		$this->data = $this->adapter->load($this->fileName);
		$data = $this->data;

		foreach ($this->root as $item) {
			$data = isset($data[$item]) ? $data[$item] : array();
		}

		return $data;
	}

	public function saveConfig()
	{
		$this->save();

		$values = $this->data;
		$this->loadConfig();
		$data = &$this->data;

		foreach ($this->root as $item) {
			$data = &$data[$item];
		}

		$data = $data ?: array();
		$data = Arrays::mergeTree($values, $data);

		file_put_contents($this->fileName, $this->adapter->dump($this->data));

		if (function_exists('opcache_reset')) {
			opcache_reset();
		}
	}

	/**
	 * @param \Nette\Forms\Container|null $container
	 * @param bool $rec
	 * @param mixed[] $values
	 * @return mixed
	 */
	private function save($container = null, $rec = false, array $values = array())
	{
		$container = $container ?: $this->container;

		if (!$rec) {
			$values = $this->loadConfig();
		} else {
			if (!isset($values[$rec])) {
				$values[$rec] = array();
			}
			$values = $values[$rec];
		}

		foreach ($container->getComponents() as $key => $control) {
			if (!Strings::startsWith($key, '_')) {
				if ($control instanceof \Nette\Forms\Container) {
					$values[$key] = $this->save($control, true, $values);
				} else if ($control instanceof \Nette\Forms\IControl) {
					if (!$control->isOmitted()) {
						$values[$key] = $control->value;
					}
				}
			}
		}

		if (!$rec) {
			$this->data = $values;
		} else {
			return $values;
		}
	}

	/**
	 * @param \Nette\Forms\Container|null $container
	 */
	private function load($container = null)
	{
		$container = $container ?: $this->container;

		$values = $this->loadConfig();
		$container->setDefaults($values);
	}

}
