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
use Venne\DataTransfer\DataTransferManager;
use Venne\Notifications\NotificationDto;
use Venne\Notifications\NotificationManager;

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

	/** @var \Venne\DataTransfer\DataTransferManager */
	private $dataTransferManager;

	public function __construct(
		EntityDao $notificationUserDao,
		NotificationManager $notificationManager,
		User $user,
		DataTransferManager $dataTransferManager
	)
	{
		parent::__construct();

		$this->user = $user;
		$this->notificationUserDao = $notificationUserDao;
		$this->notificationManager = $notificationManager;
		$this->dataTransferManager = $dataTransferManager;
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

		$entity->markRead = true;
		$this->notificationUserDao->save($entity);

		$this->redirect('this');
	}

	/**
	 * @param int $id
	 */
	public function handleRemove($id)
	{
		if (($entity = $this->notificationUserDao->find($id)) === null) {
			throw new BadRequestException;
		}

		$this->notificationUserDao->delete($entity);

		$this->redirect('this');
	}

	public function render($id)
	{
		$this->template->notification = $this->dataTransferManager
			->createQuery(NotificationDto::getClassName(), function () use ($id) {
				return $this->notificationUserDao->find($id);
			})
			->enableCache()
			->fetch();
		$this->template->render();
	}

}
