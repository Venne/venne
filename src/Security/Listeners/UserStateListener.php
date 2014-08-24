<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Security\Listeners;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Venne\Notifications\NotificationManager;
use Venne\Security\Events\RegistrationEvent;
use Venne\Security\UserEntity;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class UserStateListener
{

	/** @var \Venne\Notifications\NotificationManager */
	private $notificationManager;

	/** @var bool */
	private static $lock = false;

	public function __construct(NotificationManager $notificationManager)
	{
		$this->notificationManager = $notificationManager;
	}

	/**
	 * @param \Venne\Security\UserEntity $entity
	 * @param \Doctrine\ORM\Event\LifecycleEventArgs $event
	 */
	public function postPersist(UserEntity $entity, LifecycleEventArgs $event)
	{
		if (!self::$lock) {
			self::$lock = true;
			$this->notificationManager->notify(RegistrationEvent::getName(), $entity, 'registration', 'User has been registered.', $entity);
			self::$lock = false;
		}
	}

}
