<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Notifications\Components;

use Kdyby\Doctrine\EntityDao;
use Venne\Notifications\NotificationManager;
use Venne\System\UI\Control;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class NotificationsControl extends Control
{

	/** @var NotificationManager */
	private $notificationManager;

	/** @var EntityDao */
	private $notificationUserDao;

	/** @var INotificationControlFactory */
	private $notificationControlFactory;


	/**
	 * @param EntityDao $notificationUserDao
	 * @param NotificationManager $notificationManager
	 * @param INotificationControlFactory $notificationControlFactory
	 */
	public function __construct(
		EntityDao $notificationUserDao,
		NotificationManager $notificationManager,
		INotificationControlFactory $notificationControlFactory
	)
	{
		parent::__construct();

		$this->notificationUserDao = $notificationUserDao;
		$this->notificationManager = $notificationManager;
		$this->notificationControlFactory = $notificationControlFactory;
	}


	/**
	 * @return \Kdyby\Doctrine\EntityDao
	 */
	public function getNotificationManager()
	{
		return $this->notificationManager;
	}


	public function getNotifications()
	{
		return $this->notificationManager->getNotifications(5);
	}


	protected function createComponentNotification()
	{
		return $this->notificationControlFactory->create();
	}


	public function render()
	{
		$this->template->render();
	}

}
