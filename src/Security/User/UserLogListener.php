<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Security\User;

use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PreFlushEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use Nette\DI\Container;
use Nette\Security\User as NetteUser;
use Venne\Security\Events\LoginEvent;
use Venne\Security\User\User;

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

	public function onLoggedIn(NetteUser $netteUser)
	{
		$this->getNotificationManager()->notify(
			LoginEvent::getName(),
			$this->getUserRepository()->find($netteUser->getIdentity()->getId()),
			'login',
			'User has been logged in'
		);
	}

	public function onLoggedOut(NetteUser $netteUser)
	{
		$this->getNotificationManager()->notify(
			LoginEvent::getName(),
			$this->getUserRepository()->find($netteUser->getIdentity()->getId()),
			'logout',
			'User has been logged out'
		);
	}

	/**
	 * @return \Venne\Notifications\NotificationManager
	 */
	private function getNotificationManager()
	{
		return $this->container->getByType(\Venne\Notifications\NotificationManager::class);
	}

	/**
	 * @return \Kdyby\Doctrine\EntityRepository
	 */
	private function getUserRepository()
	{
		$entityManager = $this->container->getByType(\Doctrine\ORM\EntityManager::class);

		return $entityManager->getRepository(User::class);
	}

}
