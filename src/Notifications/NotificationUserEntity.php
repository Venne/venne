<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Notifications;

use Doctrine\ORM\Mapping as ORM;
use Kdyby\Doctrine\Entities\BaseEntity;
use Nette\InvalidArgumentException;
use Venne\Doctrine\Entities\IdentifiedEntityTrait;
use Venne\Security\UserEntity;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 * @ORM\Entity
 * @ORM\Table(name="notification_user")
 */
class NotificationUserEntity extends BaseEntity
{

	use IdentifiedEntityTrait;

	/**
	 * @var UserEntity
	 * @ORM\ManyToOne(targetEntity="\Venne\Security\UserEntity")
	 * @ORM\JoinColumn(onDelete="CASCADE")
	 */
	protected $user;

	/**
	 * @var UserEntity
	 * @ORM\ManyToOne(targetEntity="\Venne\Notifications\NotificationEntity")
	 * @ORM\JoinColumn(onDelete="CASCADE")
	 */
	protected $notification;

	/**
	 * @var bool
	 * @ORM\Column(type="boolean")
	 */
	protected $markRead = FALSE;


	/**
	 * @param NotificationEntity $notification
	 * @param UserEntity $user
	 */
	public function __construct(NotificationEntity $notification, UserEntity $user)
	{
		$this->notification = $notification;
		$this->user = $user;
	}


	/**
	 * @param NotificationEntity $log
	 */
	public function setNotification(NotificationEntity $log)
	{
		$this->notification = $log;
	}


	/**
	 * @param UserEntity $user
	 */
	public function setUser(UserEntity $user)
	{
		$this->user = $user;
	}

}

