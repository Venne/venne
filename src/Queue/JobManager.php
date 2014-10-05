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

use Doctrine\ORM\EntityManager;
use Nette\DI\Container;
use Nette\InvalidArgumentException;
use Nette\Security\User as NetteUser;
use Venne\Security\User;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class JobManager extends \Nette\Object
{

	const PRIORITY_REALTIME = 0;

	const PRIORITY_DEFAULT = 1;

	/** @var \Doctrine\ORM\EntityManager */
	private $entityManager;

	/** @var \Kdyby\Doctrine\EntityRepository */
	private $jobRepository;

	/** @var \Venne\Queue\IJob[] */
	private $jobs = array();

	/** @var \Venne\Queue\ConfigManager */
	private $configManager;

	/** @var \Nette\DI\Container */
	private $container;

	/** @var \Nette\Security\NetteUser */
	private $netteUser;

	public function __construct(
		EntityManager $entityManager,
		ConfigManager $configManager,
		NetteUser $netteUser,
		Container $container
	)
	{
		$this->entityManager = $entityManager;
		$this->jobRepository = $entityManager->getRepository(Job::class);
		$this->configManager = $configManager;
		$this->netteUser = $netteUser;
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
	 * @param \Venne\Queue\Job $jobEntity
	 */
	public function scheduleJob(Job $jobEntity, $priority = self::PRIORITY_DEFAULT)
	{
		if (!$jobEntity->user && $this->netteUser->identity instanceof User) {
			$jobEntity->user = $this->netteUser->identity;
		}

		if ($priority === self::PRIORITY_REALTIME) {
			$this->doJob($jobEntity, $priority);
		} else {
			$this->entityManager->persist($jobEntity);
			$this->entityManager->flush($jobEntity);
		}
	}

	private function doJob(Job $jobEntity, $priority)
	{
		$jobEntity->state = $jobEntity::STATE_IN_PROGRESS;
		$this->entityManager->flush($jobEntity);

		$job = $this->getJob($jobEntity->type);

		try {
			$job->run($jobEntity, $priority);
			$this->entityManager->remove($jobEntity);
			$this->entityManager->flush();
		} catch (\Exception $e) {
			$jobEntity->state = $jobEntity::STATE_FAILED;
			$this->entityManager->flush($jobEntity);

			throw new JobFailedException(sprintf('Job \'%s\' failed.', $jobEntity->getId()), 0, $e);
		}
	}

	/**
	 * @param \Venne\Queue\Worker $worker
	 * @return bool
	 * @internal
	 */
	public function doNextWork(Worker $worker)
	{
		$this->configManager->lock();
		$jobEntity = $this->jobRepository->createQueryBuilder('a')
			->addOrderBy('a.priority', 'DESC')
			->addOrderBy('a.date', 'ASC')
			->andWhere('a.date <= :now')->setParameter('now', new \DateTime)
			->andWhere('a.state = :state')->setParameter('state', Job::STATE_SCHEDULED)
			->setMaxResults(1)
			->getQuery()->getOneOrNullResult();

		$data = $this->configManager->loadConfigFile();
		$data['worker'][$worker->getId()]['lastCheck'] = (new \DateTime)->format('Y-m-d H:i:s');
		$this->configManager->saveConfigFile($data);

		if ($jobEntity) {
			$jobEntity->state = $jobEntity::STATE_IN_PROGRESS;
			$this->entityManager->flush($jobEntity);
			$this->configManager->unlock();

			try {
				$this->doJob($jobEntity, self::PRIORITY_DEFAULT);
			} catch (JobFailedException $e) {
				$failed = true;
				$jobEntity->state = $jobEntity::STATE_FAILED;
				$this->entityManager->flush($jobEntity);

				$worker->log('Error: ' . $e->getPrevious()->getMessage());
			}

			if (!isset($failed)) {
				if ($jobEntity->dateInterval && $jobEntity->round !== 0) {
					$zero = new \DateTime('00:00');
					$diff = $zero->diff($jobEntity->dateInterval);

					$jobEntity->round--;
					$jobEntity->date = $jobEntity->date->add($diff);
					$jobEntity->state = $jobEntity::STATE_SCHEDULED;
					$this->entityManager->persist($jobEntity);
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
