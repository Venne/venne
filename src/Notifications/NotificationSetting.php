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
use Venne\Security\User\User;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 *
 * @ORM\Entity
 * @ORM\Table(name="notification_setting")
 */
class NotificationSetting extends \Venne\Doctrine\Entities\BaseEntity
{

	use \Venne\Doctrine\Entities\IdentifiedEntityTrait;

	/**
	 * @var \Venne\Notifications\NotificationType
	 *
	 * @ORM\ManyToOne(targetEntity="NotificationType")
	 * @ORM\JoinColumn(onDelete="CASCADE")
	 */
	private $type;

	/**
	 * @var string|null
	 *
	 * @ORM\Column(type="string", nullable=true)
	 */
	private $target;

	/**
	 * @var \Venne\Security\User\User
	 *
	 * @ORM\ManyToOne(targetEntity="\Venne\Security\User\User")
	 * @ORM\JoinColumn(onDelete="SET NULL")
	 */
	private $targetUser;

	/**
	 * @var int|null
	 *
	 * @ORM\Column(type="integer", nullable=true)
	 */
	private $targetKey;

	/**
	 * @var \Venne\Security\User\User
	 *
	 * @ORM\ManyToOne(targetEntity="\Venne\Security\User\User")
	 * @ORM\JoinColumn(onDelete="CASCADE")
	 */
	private $user;

	/**
	 * @var bool
	 *
	 * @ORM\Column(type="boolean")
	 */
	private $email = false;

	/**
	 * @var bool
	 *
	 * @ORM\Column(type="boolean")
	 */
	private $selfNotification = false;

	/**
	 * @return \Venne\Notifications\NotificationType
	 */
	public function getType()
	{
		return $this->type;
	}

	/**
	 * @return null|string
	 */
	public function getTarget()
	{
		return $this->target;
	}

	/**
	 * @return \Venne\Security\User\User
	 */
	public function getTargetUser()
	{
		return $this->targetUser;
	}

	/**
	 * @return int|null
	 */
	public function getTargetKey()
	{
		return $this->targetKey;
	}

	/**
	 * @param \Venne\Security\User\User $user
	 */
	public function setUser(User $user)
	{
		$this->user = $user;
	}

	/**
	 * @return \Venne\Security\User\User
	 */
	public function getUser()
	{
		return $this->user;
	}

	/**
	 * @return bool
	 */
	public function isEmail()
	{
		return $this->email;
	}

	/**
	 * @return bool
	 */
	public function isSelfNotification()
	{
		return $this->selfNotification;
	}

}
