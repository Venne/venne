<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace CmsModule\Content\Listeners;

use CmsModule\Content\Entities\LogEntity;
use CmsModule\Content\Repositories\LogRepository;
use Nette\DI\Container;
use Nette\Security\User;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class UserLogListener
{

	/** @var Container|\SystemContainer */
	protected $container;


	public function __construct(Container $container)
	{
		$this->container = $container;
	}


	public function onLoggedIn(User $user)
	{
		if (!$this->container->doctrine->createCheckConnection()) {
			return;
		}

		$userEntity = $this->getUserRepository()->findOneBy(array('email' => $user->identity->id));

		if ($userEntity) {
			$logEntity = new LogEntity($userEntity, get_class($userEntity), $userEntity->id, LogEntity::ACTION_OTHER);
			$logEntity->setMessage('User has been logged in');

			$this->getLogRepository()->save($logEntity);
		}
	}


	public function onLoggedOut(User $user)
	{
		if (!$this->container->doctrine->createCheckConnection()) {
			return;
		}

		$userEntity = $this->getUserRepository()->findOneBy(array('email' => $user->identity->id));

		if ($userEntity) {
			$logEntity = new LogEntity($userEntity, get_class($userEntity), $userEntity->id, LogEntity::ACTION_OTHER);
			$logEntity->setMessage('User has been logged out');

			$this->getLogRepository()->save($logEntity);
		}
	}


	/**
	 * @return LogRepository
	 */
	private function getLogRepository()
	{
		return $this->container->cms->logRepository;
	}


	/**
	 * @return UserRepository
	 */
	private function getUserRepository()
	{
		return $this->container->cms->userRepository;
	}
}
