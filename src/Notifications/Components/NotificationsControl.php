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
use Venne\DataTransfer\DataTransferManager;
use Venne\Notifications\NotificationDto;
use Venne\Notifications\NotificationManager;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class NotificationsControl extends \Venne\System\UI\Control
{

	/** @var \Venne\Notifications\NotificationManager */
	private $notificationManager;

	/** @var \Kdyby\Doctrine\EntityDao */
	private $notificationUserDao;

	/** @var \Venne\Notifications\Components\INotificationControlFactory */
	private $notificationControlFactory;

	/** @var \Venne\DataTransfer\DataTransferManager */
	private $dataTransferManager;

	public function __construct(
		EntityDao $notificationUserDao,
		NotificationManager $notificationManager,
		INotificationControlFactory $notificationControlFactory,
		DataTransferManager $dataTransferManager
	)
	{
		parent::__construct();

		$this->notificationUserDao = $notificationUserDao;
		$this->notificationManager = $notificationManager;
		$this->notificationControlFactory = $notificationControlFactory;
		$this->dataTransferManager = $dataTransferManager;
	}

	/**
	 * @return \Venne\Notifications\Components\NotificationControl
	 */
	protected function createComponentNotification()
	{
		return $this->notificationControlFactory->create();
	}

	public function render()
	{
		$this->template->notifications = $this->dataTransferManager
			->createQuery(NotificationDto::getClassName(), function () {
				return $this->notificationManager->getNotifications(5);
			})
			->enableCache()
			->fetchAll();
		$this->template->render();
	}

}
