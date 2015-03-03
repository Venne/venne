<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\System\Invitation;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Nette\Application\Application;
use Venne\Notifications\EmailManager;
use Venne\Notifications\NotificationManager;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class InvitationStateListener
{

	/** @var \Venne\Notifications\NotificationManager */
	private $notificationManager;

	/** @var \Venne\Notifications\EmailManager */
	private $emailManager;

	/** @var \Nette\Application\Application */
	private $application;

	/** @var bool */
	private static $lock = false;

	public function __construct(
		NotificationManager $notificationManager,
		EmailManager $emailManager,
		Application $application
	) {
		$this->notificationManager = $notificationManager;
		$this->emailManager = $emailManager;
		$this->application = $application;
	}

	public function postPersist(Invitation $entity, LifecycleEventArgs $event)
	{
		if (!self::$lock) {
			self::$lock = true;
			$this->emailManager->send($entity->getEmail(), null, InvitationEvent::getName(), 'invitation', array(
				'link' => $this->application->getPresenter()->link('//:Admin:System:Login:default', array(
					'registration' => $entity->getRegistration()->getId(),
					'hash' => $entity->getHash(),
				)),
			));
			$this->notificationManager->notify(InvitationEvent::getName(), $entity, 'invitation', 'User has been invited.');
			self::$lock = false;
		}
	}

}
