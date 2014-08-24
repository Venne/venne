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
use Nette\Application\BadRequestException;
use Nette\Security\User;
use Venne\Notifications\NotificationManager;
use Venne\Notifications\NotificationUserEntity;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class NotificationControl extends \Venne\System\UI\Control
{

	/** @var \Nette\Security\User */
	private $user;

	/** @var \Venne\Notifications\NotificationManager */
	private $notificationManager;

	/** @var \Kdyby\Doctrine\EntityDao */
	private $notificationUserDao;

	public function __construct(
		EntityDao $notificationUserDao,
		NotificationManager $notificationManager,
		User $user
	)
	{
		parent::__construct();

		$this->user = $user;
		$this->notificationUserDao = $notificationUserDao;
		$this->notificationManager = $notificationManager;
	}

	/**
	 * @return \Kdyby\Doctrine\EntityDao
	 */
	public function getNotificationManager()
	{
		return $this->notificationManager;
	}

	/**
	 * @param int $id
	 */
	public function handleRead($id)
	{
		if (($entity = $this->notificationUserDao->find($id)) === null) {
			throw new BadRequestException;
		}

		if ($entity->user !== $this->user->identity) {
			throw new BadRequestException;
		}

		$entity->markRead = true;
		$this->notificationUserDao->save($entity);

		if (!$this->presenter->isAjax()) {
			$this->redirect('this');
		}
	}

	/**
	 * @param int $id
	 */
	public function handleRemove($id)
	{
		if (($entity = $this->notificationUserDao->find($id)) === null) {
			throw new BadRequestException;
		}

		if ($entity->user !== $this->user->identity) {
			throw new BadRequestException;
		}

		$this->notificationUserDao->delete($entity);

		if (!$this->presenter->isAjax()) {
			$this->redirect('this');
		}
	}

	public function render(NotificationUserEntity $notification)
	{
		$this->template->notification = $notification;
		$this->template->render();
	}

}
