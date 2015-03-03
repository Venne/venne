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

use Doctrine\ORM\EntityManager;
use Venne\Doctrine\Entities\BaseEntity;
use Nette\InvalidArgumentException;
use Nette\Security\User as NetteUser;
use Venne\Notifications\Jobs\NotificationJob;
use Venne\Queue\Job;
use Venne\Queue\JobManager;
use Venne\Security\User\User;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class NotificationManager extends \Nette\Object
{

	const PRIORITY_REALTIME = 0;

	const PRIORITY_DEFAULT = 1;

	/** @var \Kdyby\Doctrine\EntityManager */
	private $entityManager;

	/** @var \Venne\Queue\JobManager */
	private $jobManager;

	/** @var \Kdyby\Doctrine\EntityRepository */
	private $notificationUserRepository;

	/** @var \Kdyby\Doctrine\EntityRepository */
	private $settingRepository;

	/** @var \Kdyby\Doctrine\EntityRepository */
	private $typeRepository;

	/** @var \Kdyby\Doctrine\EntityRepository */
	private $userRepository;

	/** @var \Nette\Security\User */
	private $netteUser;

	/** @var \Venne\Notifications\IEvent[] */
	private $types = array();

	public function __construct(
		EntityManager $entityManager,
		NetteUser $netteUser,
		EntityManager $entityManager,
		JobManager $jobManager
	) {
		$this->notificationUserRepository = $entityManager->getRepository(NotificationUser::class);
		$this->settingRepository = $entityManager->getRepository(NotificationSetting::class);
		$this->typeRepository = $entityManager->getRepository(NotificationType::class);
		$this->userRepository = $entityManager->getRepository(User::class);
		$this->netteUser = $netteUser;
		$this->entityManager = $entityManager;
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
			throw new InvalidArgumentException(sprintf('Type \'%s\' does not exist.', $name));
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
	 * @param \Venne\Doctrine\Entities\BaseEntity|null $target
	 * @param string|null $action
	 * @param string|null $message
	 * @param \Venne\Security\User\User|null $user
	 * @param int $priority
	 */
	public function notify($type, BaseEntity $target = null, $action = null, $message = null, User $user = null, $priority = NotificationManager::PRIORITY_DEFAULT)
	{
		if (!isset($this->types[$type])) {
			throw new InvalidArgumentException(sprintf('Type \'%s\' does not exist.', $type));
		}

		if ($target && !is_object($target)) {
			throw new InvalidArgumentException('Target must be object');
		}

		$targetKey = $this->detectPrimaryKey($target);

		if ($target instanceof BaseEntity) {
			$metadata = $this->entityManager->getMetadataFactory()->getMetadataFor(get_class($target));
			$target = $metadata->getName();
		} else {
			$target = get_class($target);
		}

		$typeEntity = $this->getTypeEntity($type, $action, $message);

		$user = $user !== null ? $user : $this->getUser();
		$notification = new Notification($typeEntity, $user, $target, $targetKey);
		$this->entityManager->persist($notification);
		$this->entityManager->flush();

		$job = new Job(NotificationJob::getName(), null, array($notification->getId()));
		$job->setUser($user);

		$this->jobManager->scheduleJob($job, $priority);
	}

	/**
	 * @param int|null $limit
	 * @param int|null $offset
	 * @return NotificationUser[]
	 */
	public function getNotifications($limit = null, $offset = null)
	{
		$qb = $this->notificationUserRepository->createQueryBuilder('a')
			->leftJoin('a.notification', 'l')
			->andWhere('a.user = :user')->setParameter('user', $this->getUser()->getId())
			->orderBy('l.created', 'DESC')
			->setMaxResults($limit)
			->setFirstResult($offset);

		return $qb->getQuery()->getResult();
	}

	/**
	 * @return int
	 */
	public function countNotifications()
	{
		return $this->notificationUserRepository->createQueryBuilder('a')
			->select('COUNT(a.id)')
			->leftJoin('a.notification', 'l')
			->andWhere('a.user = :user')->setParameter('user', $this->getUser()->getId())
			->getQuery()->getSingleScalarResult();
	}

	/**
	 * @return \Venne\Security\User\User|null
	 */
	private function getUser()
	{
		return $this->netteUser->isLoggedIn() ? $this->userRepository->find($this->netteUser->getIdentity()->getId()) : null;
	}

	/**
	 * @param string $type
	 * @param string|null $action
	 * @param string|null $message
	 * @return \Venne\Notifications\NotificationType
	 */
	private function getTypeEntity($type, $action = null, $message = null)
	{
		if (($typeEntity = $this->typeRepository->findOneBy(array('type' => $type, 'action' => $action, 'message' => $message))) === null) {
			$typeEntity = new NotificationType($type, $action, $message);

			$this->entityManager->persist($typeEntity);
			$this->entityManager->flush($typeEntity);
		}

		return $typeEntity;
	}

	/**
	 * @param \Venne\Doctrine\Entities\BaseEntity $object
	 * @return string
	 */
	private function detectPrimaryKey(BaseEntity $object)
	{
		$classMetadata = $this->entityManager->getMetadataFactory()->getMetadataFor(get_class($object));
		$name = $classMetadata->getSingleIdentifierFieldName();

		return $classMetadata->getFieldValue($object, $name);
	}

}
