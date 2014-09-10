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
use Venne\Notifications\EmailManager;
use Venne\Notifications\Events\NotificationEvent;
use Venne\Queue\JobEntity;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class EmailJob extends \Venne\Queue\Job
{

	/** @var \Kdyby\Doctrine\EntityDao */
	private $notificationUserDao;

	/** @var \Kdyby\Doctrine\EntityDao */
	private $userDao;

	/** @var \Venne\Notifications\EmailManager */
	private $emailManager;

	public function __construct(
		EntityDao $notificationUserDao,
		EntityDao $userDao,
		EmailManager $emailManager
	)
	{
		$this->notificationUserDao = $notificationUserDao;
		$this->userDao = $userDao;
		$this->emailManager = $emailManager;
	}

	/**
	 * @param \Venne\Queue\JobEntity $jobEntity
	 * @param integer $priority
	 */
	public function run(JobEntity $jobEntity, $priority)
	{
		$user = $this->userDao->find($jobEntity->arguments['user']);
		$notificationUser = $this->notificationUserDao->find($jobEntity->arguments['notification']);
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
