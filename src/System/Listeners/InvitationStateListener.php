<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\System\Listeners;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Nette\Application\Application;
use Venne\Notifications\EmailManager;
use Venne\Notifications\NotificationManager;
use Venne\System\Events\InvitationEvent;
use Venne\System\InvitationEntity;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class InvitationStateListener
{

	/** @var NotificationManager */
	private $notificationManager;

	/** @var EmailManager */
	private $emailManager;

	/** @var Application */
	private $application;

	/** @var bool */
	private static $lock = FALSE;


	public function __construct(NotificationManager $notificationManager, EmailManager $emailManager, Application $application)
	{
		$this->notificationManager = $notificationManager;
		$this->emailManager = $emailManager;
		$this->application = $application;
	}


	/**
	 * @param InvitationEntity $entity
	 * @param LifecycleEventArgs $event
	 */
	public function postPersist(InvitationEntity $entity, LifecycleEventArgs $event)
	{
		if (!self::$lock) {
			self::$lock = TRUE;
			$this->emailManager->send($entity->email, NULL, InvitationEvent::getName(), 'invitation', array(
				'link' => $this->application->presenter->link('//:System:Admin:Login:default', array(
						'registrationKey' => $entity->registration->id,
						'hash' => $entity->hash,
					)),
			));
			$this->notificationManager->notify(InvitationEvent::getName(), $entity, 'invitation', 'User has been invited.');
			self::$lock = FALSE;
		}
	}

}
