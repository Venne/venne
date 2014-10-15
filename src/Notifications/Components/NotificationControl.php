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

use Doctrine\ORM\EntityManager;
use Nette\Application\BadRequestException;
use Nette\Security\User;
use Venne\DataTransfer\DataTransferManager;
use Venne\Notifications\NotificationDto;
use Venne\Notifications\NotificationManager;
use Venne\Notifications\NotificationUser;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class NotificationControl extends \Venne\System\UI\Control
{

	/** @var \Nette\Security\User */
	private $user;

	/** @var \Venne\Notifications\NotificationManager */
	private $notificationManager;

	/** @var \Doctrine\ORM\EntityManager */
	private $entityManager;

	/** @var \Kdyby\Doctrine\EntityRepository */
	private $notificationUserRepository;

	/** @var \Venne\DataTransfer\DataTransferManager */
	private $dataTransferManager;

	public function __construct(
		EntityManager $entityManager,
		NotificationManager $notificationManager,
		User $user,
		DataTransferManager $dataTransferManager
	) {
		parent::__construct();

		$this->entityManager = $entityManager;
		$this->notificationUserRepository = $entityManager->getRepository(NotificationUser::class);
		$this->user = $user;
		$this->notificationManager = $notificationManager;
		$this->dataTransferManager = $dataTransferManager;
	}

	/**
	 * @return \Venne\Notifications\NotificationManager
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
		if (($entity = $this->notificationUserRepository->find($id)) === null) {
			throw new BadRequestException;
		}

		$entity->markRead = true;
		$this->entityManager->flush($entity);

		$this->redirect('this');
	}

	/**
	 * @param int $id
	 */
	public function handleRemove($id)
	{
		if (($entity = $this->notificationUserRepository->find($id)) === null) {
			throw new BadRequestException;
		}

		$this->notificationUserRepository->delete($entity);

		$this->redirect('this');
	}

	public function render($id)
	{
		$this->template->notification = $this->dataTransferManager
			->createQuery(NotificationDto::class, function () use ($id) {
				return $this->notificationUserRepository->find($id);
			})
			->enableCache()
			->fetch();
		$this->template->render();
	}

}
