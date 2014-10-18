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
use Venne\Security\User;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 *
 * @ORM\Entity
 * @ORM\Table(name="notification_user")
 */
class NotificationUser extends \Kdyby\Doctrine\Entities\BaseEntity
{

	use \Venne\Doctrine\Entities\IdentifiedEntityTrait;

	/**
	 * @var \Venne\Security\User
	 *
	 * @ORM\ManyToOne(targetEntity="\Venne\Security\User")
	 * @ORM\JoinColumn(onDelete="CASCADE")
	 */
	protected $user;

	/**
	 * @var \Venne\Security\User
	 *
	 * @ORM\ManyToOne(targetEntity="\Venne\Notifications\Notification")
	 * @ORM\JoinColumn(onDelete="CASCADE")
	 */
	protected $notification;

	/**
	 * @var \DateTime|null
	 *
	 * @ORM\Column(type="datetime", nullable=true)
	 */
	private $read;

	public function __construct(Notification $notification, User $user)
	{
		$this->notification = $notification;
		$this->user = $user;
	}

	public function markAsRead()
	{
		$this->read = new \DateTime();
	}

	public function setNotification(Notification $log)
	{
		$this->notification = $log;
	}

	public function setUser(User $user)
	{
		$this->user = $user;
	}

	/**
	 * @return \DateTime|null
	 */
	public function getRead()
	{
		return $this->read;
	}

}
