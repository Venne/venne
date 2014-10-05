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

use Venne\Notifications\Components\INotificationControlFactory;
use Venne\Notifications\NotificationManager;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class DefaultPresenter extends \Nette\Application\UI\Presenter
{

	use \Venne\System\AdminPresenterTrait;

	/** @var \Venne\Notifications\NotificationManager */
	private $notificationManager;

	/** @var \Venne\Notifications\Components\INotificationControlFactory */
	private $notificationControlFactory;

	public function __construct(
		NotificationManager $notificationManager,
		INotificationControlFactory $notificationControlFactory
	)
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

	/**
	 * @return \Venne\Notifications\Components\NotificationControl
	 */
	protected function createComponentNotification()
	{
		return $this->notificationControlFactory->create();
	}

}
