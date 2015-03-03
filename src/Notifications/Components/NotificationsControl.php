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
use Nette\Application\UI\Multiplier;
use Venne\DataTransfer\DataTransferManager;
use Venne\Notifications\NotificationManager;
use Venne\Notifications\NotificationUser;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class NotificationsControl extends \Venne\System\UI\Control
{

	use \Venne\System\AjaxControlTrait;

	/** @var integer */
	private $offset = 0;

	/** @var \Venne\Notifications\NotificationManager */
	private $notificationManager;

	/** @var \Kdyby\Doctrine\EntityRepository */
	private $notificationUserRepository;

	/** @var \Venne\Notifications\Components\INotificationControlFactory */
	private $notificationControlFactory;

	/** @var \Venne\DataTransfer\DataTransferManager */
	private $dataTransferManager;

	public function __construct(
		EntityManager $entityManager,
		NotificationManager $notificationManager,
		INotificationControlFactory $notificationControlFactory,
		DataTransferManager $dataTransferManager
	) {
		parent::__construct();

		$this->notificationUserRepository = $entityManager->getRepository(NotificationUser::class);
		$this->notificationManager = $notificationManager;
		$this->notificationControlFactory = $notificationControlFactory;
		$this->dataTransferManager = $dataTransferManager;
	}

	/**
	 * @param int $offset
	 */
	public function handleLoad($offset)
	{
		$this->offset = (integer) $offset;
		$this->redrawControl('content');
		$this->redrawControl('js');
	}

	/**
	 * @return \Nette\Application\UI\Multiplier
	 */
	protected function createComponentNotification()
	{
		return new Multiplier(function ($id) {
			return $this->notificationControlFactory->create($id);
		});
	}

	public function render()
	{
		$this->template->notifications = $this->notificationManager->getNotifications(5, $this->offset);
		$this->template->notificationCount = $this->notificationManager->countNotifications();
		$this->template->offset = $this->offset + 5;

		$this->template->render();
	}

}
