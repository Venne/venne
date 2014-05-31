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
use Venne\Notifications\NotificationManager;
use Venne\Security\UserEntity;
use Venne\System\Events\InvitationEvent;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class InvitationStateListener
{

	/** @var NotificationManager */
	private $notificationManager;

	/** @var bool */
	private static $lock = FALSE;


	/**
	 * @param NotificationManager $notificationManager
	 */
	public function __construct(NotificationManager $notificationManager)
	{
		$this->notificationManager = $notificationManager;
	}


	/**
	 * @param UserEntity $entity
	 * @param LifecycleEventArgs $event
	 */
	public function postPersist(UserEntity $entity, LifecycleEventArgs $event)
	{
		if (!self::$lock && $entity instanceof UserEntity) {
			self::$lock = TRUE;
			$this->notificationManager->notify(InvitationEvent::getName(), $entity, 'invitation', 'User has been invited.', $entity);
			self::$lock = FALSE;
		}
	}

}
