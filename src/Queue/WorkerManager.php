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
use Nette\Utils\Random;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class WorkerManager extends \Nette\Object
{

	const STATE_STARTED = 'started';

	const STATE_PAUSED = 'paused';

	/** @var \Venne\Queue\ConfigManager */
	private $configManager;

	/** @var \Venne\Queue\IWorkerFactory */
	private $workerFactory;

	/** @var int */
	private $interval;

	/**
	 * @param int $interval
	 * @param \Venne\Queue\ConfigManager $configManager
	 * @param \Venne\Queue\IWorkerFactory $workerFactory
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
	 * @return \Venne\Queue\Worker
	 */
	public function createWorker()
	{
		$id = Random::generate(20);

		$this->configManager->lock();
		$data = $this->configManager->loadConfigFile();
		$data['worker'][$id] = array(
			'id' => $id,
			'state' => self::STATE_PAUSED,
			'lastCheck' => null,
			'lastJob' => null,
		);
		$this->configManager->saveConfigFile($data);
		$this->configManager->unlock();

		return $this->getWokrer($id);
	}

	/**
	 * @param \Venne\Queue\Worker $worker
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
	 * @param \Venne\Queue\Worker $worker
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
	 * @param \Venne\Queue\Worker $worker
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
	 * @param int $id
	 * @return \Venne\Queue\Worker
	 */
	public function getWokrer($id)
	{
		$data = $this->configManager->loadConfigFile();

		if (!isset($data['worker'][$id])) {
			throw new InvalidArgumentException("Worker '$id' does not exist.");
		}

		return $this->workerFactory->create($id, $this->interval);
	}

	/**
	 * @return string[]
	 */
	public function getWorkers()
	{
		$data = $this->configManager->loadConfigFile();

		return isset($data['worker']) ? $data['worker'] : array();
	}

}
