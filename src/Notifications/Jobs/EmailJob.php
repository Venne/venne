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
use Venne\Notifications\EmailManager;
use Venne\Notifications\Events\NotificationEvent;
use Venne\Notifications\NotificationUser;
use Venne\Queue\Job;
use Venne\Security\User\User;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class EmailJob extends \Venne\Queue\JobType
{

	/** @var \Kdyby\Doctrine\EntityRepository */
	private $notificationUserRepository;

	/** @var \Kdyby\Doctrine\EntityRepository */
	private $userRepository;

	/** @var \Venne\Notifications\EmailManager */
	private $emailManager;

	public function __construct(
		EntityManager $entityManager,
		EmailManager $emailManager
	) {
		$this->notificationUserRepository = $entityManager->getRepository(NotificationUser::class);
		$this->userRepository = $entityManager->getRepository(User::class);
		$this->emailManager = $emailManager;
	}

	/**
	 * @param \Venne\Queue\Job $job
	 * @param int $priority
	 */
	public function run(Job $job, $priority)
	{
		$user = $this->userRepository->find($job->arguments['user']);
		$notificationUser = $this->notificationUserRepository->find($job->arguments['notification']);
		$notification = $notificationUser->getNotification();

		$this->emailManager->send($user->email, $user->name, NotificationEvent::getName(), 'userNotification', array(
			'type' => $notification->type->type,
			'action' => $notification->type->action,
			'message' => $notification->type->message,
			'user' => $notification->user,
			'notification' => $notification,
			'notificationManager' => $this,
		));
	}

}
