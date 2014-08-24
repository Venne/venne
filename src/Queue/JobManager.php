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

use Kdyby\Doctrine\EntityDao;
use Nette\DI\Container;
use Nette\InvalidArgumentException;
use Nette\Security\User;
use Venne\Security\UserEntity;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class JobManager extends \Nette\Object
{

	/** @var \Kdyby\Doctrine\EntityDao */
	private $jobDao;

	/** @var \Venne\Queue\IJob[] */
	private $jobs = array();

	/** @var \Venne\Queue\ConfigManager */
	private $configManager;

	/** @var \Nette\DI\Container */
	private $container;

	/** @var \Nette\Security\User */
	private $user;

	public function __construct(
		EntityDao $jobDao,
		ConfigManager $configManager,
		User $user,
		Container $container
	)
	{
		$this->configManager = $configManager;
		$this->jobDao = $jobDao;
		$this->user = $user;
		$this->container = $container;
	}

	/**
	 * @param mixed $job
	 * @return $this
	 */
	public function registerJob($job)
	{
		$key = is_object($job) ? get_class($job) : $job;
		$this->jobs[$key] = $job;

		return $this;
	}

	/**
	 * @return \Venne\Queue\IJob[]
	 */
	public function getJobs()
	{
		return $this->jobs;
	}

	/**
	 * @param \Venne\Queue\JobEntity $workEntity
	 * @return $this
	 */
	public function scheduleJob(JobEntity $workEntity)
	{
		if (!$workEntity->user && $this->user->identity instanceof UserEntity) {
			$workEntity->user = $this->user->identity;
		}

		$this->jobDao->save($workEntity);

		return $this;
	}

	/**
	 * @param \Venne\Queue\Worker $worker
	 * @return bool
	 */
	public function doJob(Worker $worker)
	{
		$this->configManager->lock();
		$jobEntity = $this->jobDao->createQueryBuilder('a')
			->addOrderBy('a.priority', 'DESC')
			->addOrderBy('a.date', 'ASC')
			->andWhere('a.date <= :now')->setParameter('now', new \DateTime)
			->andWhere('a.state = :state')->setParameter('state', JobEntity::STATE_SCHEDULED)
			->setMaxResults(1)
			->getQuery()->getOneOrNullResult();

		$data = $this->configManager->loadConfigFile();
		$data['worker'][$worker->getId()]['lastCheck'] = (new \DateTime)->format('Y-m-d H:i:s');
		$this->configManager->saveConfigFile($data);

		if ($jobEntity) {
			$jobEntity->state = $jobEntity::STATE_IN_PROGRESS;
			$this->jobDao->save($jobEntity);
			$this->configManager->unlock();

			$job = $this->getJob($jobEntity->type);

			try {
				$job->run($jobEntity);
			} catch (\Exception $e) {
				$failed = true;
				$jobEntity->state = $jobEntity::STATE_FAILED;
				$this->jobDao->save($jobEntity);

				$worker->log('Error: ' . $e->getMessage());
			}

			if (!isset($failed)) {
				if ($jobEntity->dateInterval && $jobEntity->round !== 0) {
					$zero = new \DateTime('00:00');
					$diff = $zero->diff($jobEntity->dateInterval);

					$jobEntity->round--;
					$jobEntity->date = $jobEntity->date->add($diff);
					$jobEntity->state = $jobEntity::STATE_SCHEDULED;
					$this->jobDao->save($jobEntity);
				} else {
					$this->jobDao->delete($jobEntity);
				}
			}

			$this->configManager->lock();
			$data = $this->configManager->loadConfigFile();
			$data['worker'][$worker->getId()]['lastJob'] = (new \DateTime)->format('Y-m-d H:i:s');
			$this->configManager->saveConfigFile($data);
			$this->configManager->unlock();

			return true;
		} else {
			$this->configManager->unlock();
		}

		return false;
	}

	/**
	 * @param string $type
	 * @return object|\Venne\Queue\IJob
	 */
	public function getJob($type)
	{
		if (!isset($this->jobs[$type])) {
			throw new InvalidArgumentException(sprintf('Job type \'%s\' does not exist.', $type));
		}

		$ret = $this->jobs[$type];

		if (!is_object($ret)) {
			$ret = $this->container->getByType($ret);
		}

		return $ret;

	}

}
