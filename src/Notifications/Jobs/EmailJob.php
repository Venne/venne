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
use Venne\Queue\Job;
use Venne\Queue\JobEntity;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class EmailJob extends Job
{

	/** @var EntityDao */
	private $notificationUserDao;

	/** @var EntityDao */
	private $userDao;

	/** @var EmailManager */
	private $emailManager;


	/**
	 * @param EntityDao $notificationUserDao
	 * @param EntityDao $userDao
	 * @param EmailManager $emailManager
	 */
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
	 * @param JobEntity $jobEntity
	 */
	public function run(JobEntity $jobEntity)
	{
		$user = $this->userDao->find($jobEntity->arguments['user']);
		$notification = $this->notificationUserDao->find($jobEntity->arguments['notification']);

		$this->emailManager->send($user->email, $user->name, $notification->type->type, $notification->type->action, array(
			'notification' => $notification,
			'notificationManager' => $this,
		));
	}

}
