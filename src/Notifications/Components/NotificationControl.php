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

	use \Venne\System\AjaxControlTrait;

	/** @var integer */
	private $id;

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

	/**
	 * @param integer $id
	 * @param \Doctrine\ORM\EntityManager $entityManager
	 * @param \Venne\Notifications\NotificationManager $notificationManager
	 * @param \Nette\Security\User $user
	 * @param \Venne\DataTransfer\DataTransferManager $dataTransferManager
	 */
	public function __construct(
		$id,
		EntityManager $entityManager,
		NotificationManager $notificationManager,
		User $user,
		DataTransferManager $dataTransferManager
	) {
		parent::__construct();

		$this->id = $id;
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

	public function handleRead()
	{
		if (($entity = $this->notificationUserRepository->find($this->id)) === null) {
			throw new BadRequestException;
		}

		$entity->markRead = true;
		$this->entityManager->flush($entity);

		$this->redirect('this');
	}

	public function handleRemove()
	{
		if (($entity = $this->notificationUserRepository->find($this->id)) === null) {
			throw new BadRequestException;
		}

		$this->notificationUserRepository->delete($entity);

		$this->redirect('this');
		$this->redrawControl('content');
		$this->id = null;
	}

	public function render()
	{
		if ($this->id !== null) {
			$this->template->notification = $this->dataTransferManager
				->createQuery(NotificationDto::class, function () {
					return $this->notificationUserRepository->find($this->id);
				})
				->enableCache()
				->fetch();
		}

		$this->template->render();
	}

}
