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
use Nette\Security\User;
use Venne\Notifications\Jobs\NotificationJob;
use Venne\Notifications\Jobs\NotifyJob;
use Venne\Queue\JobEntity;
use Venne\Queue\JobManager;
use Venne\Security\SecurityManager;
use Venne\Security\UserEntity;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class NotificationManager extends \Nette\Object
{

	/** @var \Venne\Security\SecurityManager */
	private $securityManager;

	/** @var \Kdyby\Doctrine\EntityManager */
	private $entityManager;

	/** @var \Venne\Queue\JobManager */
	private $jobManager;

	/** @var \Kdyby\Doctrine\EntityDao */
	private $logDao;

	/** @var \Kdyby\Doctrine\EntityDao */
	private $notificationDao;

	/** @var \Kdyby\Doctrine\EntityDao */
	private $settingDao;

	/** @var \Kdyby\Doctrine\EntityDao */
	private $typeDao;

	/** @var \Kdyby\Doctrine\EntityDao */
	private $userDao;

	/** @var \Nette\Security\User */
	private $user;

	/** @var \Venne\Notifications\IEvent[] */
	private $types = array();

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
	 * @param \Venne\Notifications\IEvent $event
	 * @return $this
	 */
	public function addType(IEvent $event)
	{
		$this->types[$event->getName()] = $event;

		return $this;
	}

	/**
	 * @param $name
	 * @return \Venne\Notifications\IEvent
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
	 * @param string $type
	 * @param string|null $target
	 * @param string|null $action
	 * @param string|null $message
	 * @param \Venne\Security\UserEntity|null $user
	 */
	public function notify($type, $target = null, $action = null, $message = null, UserEntity $user = null)
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
		$notificationEntity->user = $user = $user ?: $this->getUser();
		$notificationEntity->target = $target;
		$notificationEntity->targetKey = $targetKey;
		$this->logDao->save($notificationEntity);

		$jobEntity = new JobEntity(NotificationJob::getName(), null, array($notificationEntity->id));
		$jobEntity->user = $user;

		$this->jobManager->scheduleJob($jobEntity);
	}

	/**
	 * @param int|null $limit
	 * @return NotificationUserEntity[]
	 */
	public function getNotifications($limit = null)
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
	 * @return \Venne\Security\UserEntity|null
	 */
	private function getUser()
	{
		return $this->user->identity instanceof UserEntity ? $this->user->identity : null;
	}

	/**
	 * @param string $type
	 * @param string|null $action
	 * @param string|null $message
	 * @return \Venne\Notifications\NotificationTypeEntity
	 */
	private function getTypeEntity($type, $action = null, $message = null)
	{
		if (($typeEntity = $this->typeDao->findOneBy(array('type' => $type, 'action' => $action, 'message' => $message))) === null) {
			$typeEntity = new NotificationTypeEntity;
			$typeEntity->type = $type;
			$typeEntity->action = $action;
			$typeEntity->message = $message;
			$this->typeDao->save($typeEntity);
		}

		return $typeEntity;
	}

	/**
	 * @param \Kdyby\Doctrine\Entities\BaseEntity $object
	 * @return string
	 */
	private function detectPrimaryKey(BaseEntity $object)
	{
		if ($object instanceof BaseEntity) {
			$meta = $this->entityManager->getClassMetadata(get_class($object));
			$name = $meta->getSingleIdentifierFieldName();

			return $meta->getFieldValue($object, $name);
		}
	}

}

