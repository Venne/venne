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

use Nette\DI\Container;
use Nette\Security\User;
use Venne\Security\Events\LoginEvent;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class UserLogListener implements \Kdyby\Events\Subscriber
{

	/** @var \Venne\Notifications\NotificationManager */
	private $container;

	/**
	 * @param \Nette\DI\Container $container
	 */
	public function __construct(Container $container)
	{
		$this->container = $container;
	}

	/**
	 * @return string[]
	 */
	public function getSubscribedEvents()
	{
		return array(
			'Nette\Security\User::onLoggedIn' => 'onLoggedIn',
			'Nette\Security\User::onLoggedOut' => 'onLoggedOut',
		);
	}

	public function onLoggedIn(User $user)
	{
		$this->getNotificationManager()->notify(LoginEvent::getName(), $user->identity, 'login', 'User has been logged in');
	}

	public function onLoggedOut(User $user)
	{
		$this->getNotificationManager()->notify(LoginEvent::getName(), $user->identity, 'logout', 'User has been logged out');
	}

	/**
	 * @return \Venne\Notifications\NotificationManager
	 */
	private function getNotificationManager()
	{
		return $this->container->getByType('Venne\Notifications\NotificationManager');
	}

}
