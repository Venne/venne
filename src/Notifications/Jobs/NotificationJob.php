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

use Kdyby\Doctrine\EntityDao;
use Venne\Notifications\NotificationUserEntity;
use Venne\Queue\Job;
use Venne\Queue\JobEntity;
use Venne\Queue\JobManager;
use Venne\Security\UserEntity;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class NotificationJob extends Job
{

	/** @var \Kdyby\Doctrine\EntityDao */
	private $notificationDao;

	/** @var \Kdyby\Doctrine\EntityDao */
	private $notificationUserDao;

	/** @var \Kdyby\Doctrine\EntityDao */
	private $settingDao;

	/** @var \Venne\Queue\JobManager */
	private $jobManager;

	public function __construct(
		EntityDao $notificationDao,
		EntityDao $userDao,
		EntityDao $settingDao,
		JobManager $jobManager
	)
	{
		$this->notificationDao = $notificationDao;
		$this->notificationUserDao = $userDao;
		$this->settingDao = $settingDao;
		$this->jobManager = $jobManager;
	}

	public function run(JobEntity $jobEntity)
	{
		if (($notificationEntity = $this->notificationDao->find($jobEntity->arguments[0])) === null) {
			return;
		}

		$qb = $this->settingDao->createQueryBuilder('a')
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
			$user = $setting->user ?: $notificationEntity->user;

			if (isset($users[$user->id]) && !$users[$user->id]['email']) {
				$users[$user->id]['email'] = $setting->email;
			} else {
				$users[$user->id] = array(
					'user' => $user,
					'email' => $setting->email,
				);
			}
		}

		foreach ($users as $user) {
			$notificationUserEntity = new NotificationUserEntity($notificationEntity, $user['user']);
			$this->notificationUserDao->save($notificationUserEntity);

			if ($user['email']) {
				$this->jobManager->scheduleJob(new JobEntity(EmailJob::getName(), null, array(
					'user' => $user['user'] instanceof UserEntity ? $user['user']->id : $user['user'],
					'notification' => $notificationUserEntity->id,
				)));
			}
		}
	}

}
