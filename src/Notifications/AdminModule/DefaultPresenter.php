<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Notifications\AdminModule;

use Nette\Application\UI\Presenter;
use Venne\Notifications\Components\INotificationControlFactory;
use Venne\Notifications\NotificationManager;
use Venne\System\AdminPresenterTrait;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 *
 * @secured
 */
class DefaultPresenter extends Presenter
{

	use AdminPresenterTrait;

	/** @var NotificationManager */
	private $notificationManager;

	/** @var INotificationControlFactory */
	private $notificationControlFactory;


	/**
	 * @param NotificationManager $notificationManager
	 * @param INotificationControlFactory $notificationControlFactory
	 */
	public function __construct(NotificationManager $notificationManager, INotificationControlFactory $notificationControlFactory)
	{
		$this->notificationManager = $notificationManager;
		$this->notificationControlFactory = $notificationControlFactory;
	}


	/**
	 * @return \Venne\Notifications\NotificationManager
	 */
	public function getNotificationManager()
	{
		return $this->notificationManager;
	}


	protected function createComponentNotification()
	{
		return $this->notificationControlFactory->create();
	}

}
