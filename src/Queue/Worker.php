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

use Nette\Object;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class Worker extends Object
{

	/** @var int */
	private $id;

	/** @var string */
	private $configDir;

	/** @var JobManager */
	private $jobManager;


	/**
	 * @param $id
	 * @param $configDir
	 * @param JobManager $jobManager
	 */
	public function __construct($id, $configDir, JobManager $jobManager)
	{
		$this->jobManager = $jobManager;
		$this->configDir = $configDir;
		$this->id = $id;
	}


	/**
	 * @return int
	 */
	public function getId()
	{
		return $this->id;
	}


	/**
	 * @return bool
	 */
	public function run()
	{
		$this->log('check jobs!');
		return $this->jobManager->doJob($this);
	}


	private function getLogFile()
	{
		return $this->configDir . '/worker_' . $this->id . '.log';
	}


	public function log($message)
	{
		if (file_exists($this->getLogFile())) {
			$data = file_get_contents($this->getLogFile());
			if (($count = substr_count($data, "\n")) > 1000) {
				for ($x = 1000; $x < $count; $x++) {
					$data = substr($data, strpos($data, "\n") + 1);
				}
			}
		} else {
			$data = '';
		}

		$data .= date("Y-m-d H:i:s") . ': ' . $message . "\n";
		file_put_contents($this->getLogFile(), $data);
	}

}
