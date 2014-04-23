<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Queue;

use Nette\InvalidArgumentException;
use Nette\Object;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class ConfigManager extends Object
{

	/** @var string */
	private $configDir;

	private $lock;


	/**
	 * @param $configDir
	 */
	public function __construct($configDir)
	{
		$this->setConfigDir($configDir);
	}


	/**
	 * @param string $configDir
	 */
	public function setConfigDir($configDir)
	{
		$this->configDir = $configDir;

		if (!file_exists($configDir)) {
			mkdir($configDir, 0777, TRUE);
		}

		if (!file_exists($this->getConfigFile())) {
			$this->saveConfigFile();
		}
	}


	public function lock()
	{
		if (!$this->lock) {
			$this->lock = fopen($this->getLockFile(), 'w+');
		}

		flock($this->lock, LOCK_EX);
	}


	public function unlock()
	{
		if (!$this->lock) {
			return;
		}

		flock($this->lock, LOCK_UN);
		$this->lock = NULL;
	}


	private function getConfigFile()
	{
		return $this->configDir . '/config.json';
	}


	public function loadConfigFile()
	{
		return json_decode(file_get_contents($this->getConfigFile()), TRUE);
	}


	public function saveConfigFile(array $data = array())
	{
		file_put_contents($this->getConfigFile(), json_encode($data, JSON_PRETTY_PRINT));
	}


	private function getLockFile()
	{
		return $this->configDir . '/lock';
	}


	public function __destruct()
	{
		if ($this->lock) {
			$this->unlock();
		}
	}

}
