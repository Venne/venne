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
use Nette\Utils\Random;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class WorkerManager extends Object
{

	const STATE_STARTED = 'started';

	const STATE_PAUSED = 'paused';

	/** @var ConfigManager */
	private $configManager;

	/** @var IWorkerFactory */
	private $workerFactory;

	/** @var int */
	private $interval;


	/**
	 * @param $interval
	 * @param ConfigManager $configManager
	 * @param IWorkerFactory $workerFactory
	 */
	public function __construct($interval, ConfigManager $configManager, IWorkerFactory $workerFactory)
	{
		$this->interval = $interval;
		$this->configManager = $configManager;
		$this->workerFactory = $workerFactory;
	}


	/**
	 * @return int
	 */
	public function getInterval()
	{
		return $this->interval;
	}


	/**
	 * @return Worker
	 */
	public function createWorker()
	{
		$id = Random::generate(20);

		$this->configManager->lock();
		$data = $this->configManager->loadConfigFile();
		$data['worker'][$id] = array(
			'id' => $id,
			'state' => self::STATE_PAUSED,
			'lastCheck' => NULL,
			'lastJob' => NULL,
		);
		$this->configManager->saveConfigFile($data);
		$this->configManager->unlock();

		return $this->getWokrer($id);
	}


	/**
	 * @param Worker $worker
	 */
	public function startWorker(Worker $worker)
	{
		$this->configManager->lock();
		$data = $this->configManager->loadConfigFile();
		$data['worker'][$worker->getId()]['state'] = self::STATE_STARTED;
		$this->configManager->saveConfigFile($data);
		$worker->log('Worker has been started');
		$this->configManager->unlock();
	}


	/**
	 * @param Worker $worker
	 */
	public function stopWorker(Worker $worker)
	{
		$this->configManager->lock();
		$data = $this->configManager->loadConfigFile();
		unset($data['worker'][$worker->getId()]);
		$this->configManager->saveConfigFile($data);
		$worker->log('Worker has been stopped');
		$this->configManager->unlock();
	}


	/**
	 * @param Worker $worker
	 */
	public function pauseWorker(Worker $worker)
	{
		$this->configManager->lock();
		$data = $this->configManager->loadConfigFile();
		$data['worker'][$worker->getId()]['state'] = self::STATE_PAUSED;
		$this->configManager->saveConfigFile($data);
		$worker->log('Worker has been paused');
		$this->configManager->unlock();
	}


	/**
	 * @param $id
	 * @return Worker
	 * @throws \Nette\InvalidArgumentException
	 */
	public function getWokrer($id)
	{
		$data = $this->configManager->loadConfigFile();

		if (!isset($data['worker'][$id])) {
			throw new InvalidArgumentException("Worker '$id' does not exist.");
		}

		return $this->workerFactory->create($id, $this->interval);
	}


	public function getWorkers()
	{
		$data = $this->configManager->loadConfigFile();
		return isset($data['worker']) ? $data['worker'] : array();
	}

}
