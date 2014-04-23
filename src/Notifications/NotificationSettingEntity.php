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
 * @ORM\Table(name="notification_setting")
 */
class NotificationSettingEntity extends BaseEntity
{

	use IdentifiedEntityTrait;


	/**
	 * @var NotificationTypeEntity
	 * @ORM\ManyToOne(targetEntity="NotificationTypeEntity")
	 * @ORM\JoinColumn(onDelete="CASCADE")
	 */
	protected $type;

	/**
	 * @var string
	 * @ORM\Column(type="string", nullable=true)
	 */
	protected $target;

	/**
	 * @var UserEntity
	 * @ORM\ManyToOne(targetEntity="\Venne\Security\UserEntity")
	 * @ORM\JoinColumn(onDelete="SET NULL")
	 */
	protected $targetUser;


	/**
	 * @var int
	 * @ORM\Column(type="integer", nullable=true)
	 */
	protected $targetKey;

	/**
	 * @var UserEntity
	 * @ORM\ManyToOne(targetEntity="\Venne\Security\UserEntity")
	 * @ORM\JoinColumn(onDelete="CASCADE")
	 */
	protected $user;

	/**
	 * @var bool
	 * @ORM\Column(type="boolean")
	 */
	protected $email = FALSE;

	/**
	 * @var bool
	 * @ORM\Column(type="boolean")
	 */
	protected $selfNotification = FALSE;

}

