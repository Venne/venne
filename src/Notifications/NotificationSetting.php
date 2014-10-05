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

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 *
 * @ORM\Entity
 * @ORM\Table(name="notification_setting")
 */
class NotificationSetting extends \Kdyby\Doctrine\Entities\BaseEntity
{

	use \Venne\Doctrine\Entities\IdentifiedEntityTrait;

	/**
	 * @var \Venne\Notifications\NotificationType
	 *
	 * @ORM\ManyToOne(targetEntity="NotificationType")
	 * @ORM\JoinColumn(onDelete="CASCADE")
	 */
	protected $type;

	/**
	 * @var string|null
	 *
	 * @ORM\Column(type="string", nullable=true)
	 */
	protected $target;

	/**
	 * @var \Venne\Security\User
	 *
	 * @ORM\ManyToOne(targetEntity="\Venne\Security\User")
	 * @ORM\JoinColumn(onDelete="SET NULL")
	 */
	protected $targetUser;

	/**
	 * @var int|null
	 *
	 * @ORM\Column(type="integer", nullable=true)
	 */
	protected $targetKey;

	/**
	 * @var \Venne\Security\User
	 *
	 * @ORM\ManyToOne(targetEntity="\Venne\Security\User")
	 * @ORM\JoinColumn(onDelete="CASCADE")
	 */
	protected $user;

	/**
	 * @var bool
	 *
	 * @ORM\Column(type="boolean")
	 */
	protected $email = false;

	/**
	 * @var bool
	 *
	 * @ORM\Column(type="boolean")
	 */
	protected $selfNotification = false;

}
