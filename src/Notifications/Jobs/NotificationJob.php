<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Notifications\Jobs;

use Doctrine\ORM\EntityManager;
use Venne\Notifications\Notification;
use Venne\Notifications\NotificationSetting;
use Venne\Notifications\NotificationUser;
use Venne\Queue\Job;
use Venne\Queue\JobManager;
use Venne\Security\User;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class NotificationJob extends \Venne\Queue\JobType
{

	/** @var \Doctrine\ORM\EntityManager */
	private $entityManager;

	/** @var \Kdyby\Doctrine\EntityRepository */
	private $notificationRepository;

	/** @var \Kdyby\Doctrine\EntityRepository */
	private $notificationUserRepository;

	/** @var \Kdyby\Doctrine\EntityRepository */
	private $notificationSettingRepository;

	/** @var \Venne\Queue\JobManager */
	private $jobManager;

	public function __construct(
		EntityManager $entityManager,
		JobManager $jobManager
	)
	{
		$this->entityManager = $entityManager;
		$this->notificationRepository = $entityManager->getRepository(Notification::class);
		$this->notificationUserRepository = $entityManager->getRepository(NotificationUser::class);
		$this->notificationSettingRepository = $entityManager->getRepository(NotificationSetting::class);
		$this->jobManager = $jobManager;
	}

	/**
	 * @param \Venne\Queue\Job $job
	 * @param integer $priority
	 */
	public function run(Job $job, $priority)
	{
		/** @var Notification $notificationEntity */
		$notificationEntity = $this->notificationRepository->find($job->arguments[0]);

		if ($notificationEntity === null) {
			return;
		}

		$qb = $this->notificationSettingRepository->createQueryBuilder('a')
			->andWhere('a.type IS NULL OR a.type = :type')->setParameter('type', $notificationEntity->type->id);

		if ($notificationEntity->user) {
			$qb = $qb->andWhere('a.targetUser IS NULL OR a.targetUser = :targetUser')->setParameter('targetUser', $notificationEntity->user->id);
		}

		if ($notificationEntity->target) {
			$qb = $qb->andWhere('a.target IS NULL OR a.target = :target')->setParameter('target', $notificationEntity->target);
		}

		if ($notificationEntity->targetKey) {
			$qb = $qb->andWhere('a.targetKey IS NULL OR a.targetKey = :targetKey')->setParameter('targetKey', $notificationEntity->targetKey);
		}

		$users = array();
		foreach ($qb->getQuery()->getResult() as $setting) {
			$user = $setting->user !== null ? $setting->user : $notificationEntity->user;

			if (isset($users[$user->id]) && !$users[$user->id]['email']) {
				$users[$user->id]['email'] = $setting->email;
			} else {
				$users[$user->id] = array(
					'user' => $user,
					'email' => $setting->user !== null ? $setting->email : $notificationEntity->user->getEmail(),
				);
			}
		}

		foreach ($users as $user) {
			$notificationUser = new NotificationUser($notificationEntity, $user['user']);

			$this->entityManager->persist($notificationUser);
			$this->entityManager->flush($notificationUser);

			if ($user['email']) {
				$this->jobManager->scheduleJob(new Job(EmailJob::getName(), null, array(
					'user' => $user['user'] instanceof User ? $user['user']->id : $user['user'],
					'notification' => $notificationUser->id,
				)), $priority);
			}
		}
	}

}
