<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Notifications;

use Kdyby\Doctrine\Entities\BaseEntity;
use Kdyby\Doctrine\EntityDao;
use Kdyby\Doctrine\EntityManager;
use Nette\InvalidArgumentException;
use Nette\Object;
use Nette\Security\User;
use Venne\Notifications\Jobs\NotificationJob;
use Venne\Notifications\Jobs\NotifyJob;
use Venne\Security\SecurityManager;
use Venne\Security\UserEntity;
use Venne\Queue\JobEntity;
use Venne\Queue\JobManager;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class NotificationManager extends Object
{

	/** @var SecurityManager */
	private $securityManager;

	/** @var EntityManager */
	private $entityManager;

	/** @var JobManager */
	private $jobManager;

	/** @var EntityDao */
	private $logDao;

	/** @var EntityDao */
	private $notificationDao;

	/** @var EntityDao */
	private $settingDao;

	/** @var EntityDao */
	private $typeDao;

	/** @var EntityDao */
	private $userDao;

	/** @var User */
	private $user;

	/** @var IEvent[] */
	private $types = array();


	/**
	 * @param EntityDao $logDao
	 * @param EntityDao $notificationDao
	 * @param EntityDao $settingDao
	 * @param EntityDao $typeDao
	 * @param EntityDao $userDao
	 * @param User $user
	 * @param EntityManager $entityManager
	 * @param SecurityManager $securityManager
	 * @param JobManager $jobManager
	 */
	public function __construct(
		EntityDao $logDao,
		EntityDao $notificationDao,
		EntityDao $settingDao,
		EntityDao $typeDao,
		EntityDao $userDao,
		User $user,
		EntityManager $entityManager,
		SecurityManager $securityManager,
		JobManager $jobManager
	)
	{
		$this->logDao = $logDao;
		$this->notificationDao = $notificationDao;
		$this->settingDao = $settingDao;
		$this->typeDao = $typeDao;
		$this->userDao = $userDao;
		$this->user = $user;
		$this->entityManager = $entityManager;
		$this->securityManager = $securityManager;
		$this->jobManager = $jobManager;
	}


	/**
	 * @param IEvent $event
	 * @return $this
	 */
	public function addType(IEvent $event)
	{
		$this->types[$event->getName()] = $event;
		return $this;
	}


	/**
	 * @param $name
	 * @return IEvent
	 * @throws \Nette\InvalidArgumentException
	 */
	public function getType($name)
	{
		if (!isset($this->types[$name])) {
			throw new InvalidArgumentException("Type '$name' does not exist.");
		}

		return $this->types[$name];
	}


	/**
	 * @return \Venne\Notifications\IEvent[]
	 */
	public function getTypes()
	{
		return $this->types;
	}


	/**
	 * @param $type
	 * @param null $target
	 * @param null $action
	 * @param null $message
	 * @param UserEntity $entity
	 * @throws \Nette\InvalidArgumentException
	 */
	public function log($type, $target = NULL, $action = NULL, $message = NULL, UserEntity $user = NULL)
	{
		if (!isset($this->types[$type])) {
			throw new InvalidArgumentException("Type '$type' does not exist.");
		}

		if ($target && !is_object($target)) {
			throw new InvalidArgumentException("Target must be object");
		}

		$targetKey = $this->detectPrimaryKey($target);

		if ($target instanceof BaseEntity) {
			$metadata = $this->entityManager->getClassMetadata(get_class($target));
			$target = $metadata->getName();
		} else {
			$target = get_class($target);
		}

		$typeEntity = $this->getTypeEntity($type, $action, $message);

		$notificationEntity = new NotificationEntity($typeEntity);
		$notificationEntity->user = $user ?: $this->getUser();
		$notificationEntity->target = $target;
		$notificationEntity->targetKey = $targetKey;
		$this->logDao->save($notificationEntity);

		$this->jobManager->scheduleJob(new JobEntity(NotificationJob::getName(), NULL, array($notificationEntity->id)));
	}


	/**
	 * @param null $limit
	 * @return NotificationUserEntity[]
	 */
	public function getNotifications($limit = NULL)
	{
		return $this->notificationDao->createQueryBuilder('a')
			->leftJoin('a.notification', 'l')
			->andWhere('a.user = :user')->setParameter('user', $this->getUser()->id)
			->orderBy('l.created', 'DESC')
			->setMaxResults($limit)
			->getQuery()->getResult();
	}


	/**
	 * @return int
	 */
	public function countNotifications()
	{
		return $this->notificationDao->createQueryBuilder('a')
			->select('COUNT(a.id)')
			->leftJoin('a.notification', 'l')
			->andWhere('a.user = :user')->setParameter('user', $this->getUser()->id)
			->getQuery()->getSingleScalarResult();
	}


	/**
	 * @return UserEntity|null
	 */
	private function getUser()
	{
		return $this->user->identity instanceof UserEntity ? $this->user->identity : NULL;
	}


	/**
	 * @param $type
	 * @param null $action
	 * @param null $message
	 * @return NotificationTypeEntity
	 */
	private function getTypeEntity($type, $action = NULL, $message = NULL)
	{
		if (($typeEntity = $this->typeDao->findOneBy(array('type' => $type, 'action' => $action, 'message' => $message))) === NULL) {
			$typeEntity = new NotificationTypeEntity;
			$typeEntity->type = $type;
			$typeEntity->action = $action;
			$typeEntity->message = $message;
			$this->typeDao->save($typeEntity);
		}

		return $typeEntity;
	}


	/**
	 * @param $object
	 * @return mixed
	 */
	private function detectPrimaryKey($object)
	{
		if ($object instanceof BaseEntity) {
			$meta = $this->entityManager->getClassMetadata(get_class($object));
			$name = $meta->getSingleIdentifierFieldName();
			return $meta->getFieldValue($object, $name);
		}
	}

}

