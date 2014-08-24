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

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Venne\Security\UserEntity;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 *
 * @property \Venne\Notifications\NotificationTypeEntity $type
 * @property \Venne\Security\UserEntity $user
 * @property \DateTime $created
 * @property string $target
 * @property int $targetKey
 *
 * @ORM\Entity
 * @ORM\Table(name="notification", indexes={@ORM\Index(name="created_idx", columns={"created"})})
 */
class NotificationEntity extends \Kdyby\Doctrine\Entities\BaseEntity
{

	use \Venne\Doctrine\Entities\IdentifiedEntityTrait;

	/**
	 * @var \Venne\Notifications\NotificationTypeEntity
	 *
	 * @ORM\ManyToOne(targetEntity="NotificationTypeEntity")
	 * @ORM\JoinColumn(onDelete="CASCADE")
	 */
	protected $type;

	/**
	 * @var \Venne\Security\UserEntity
	 *
	 * @ORM\ManyToOne(targetEntity="\Venne\Security\UserEntity")
	 * @ORM\JoinColumn(onDelete="CASCADE")
	 */
	protected $user;

	/**
	 * @var \DateTime
	 *
	 * @ORM\Column(type="datetime")
	 */
	protected $created;

	/**
	 * @var string|null
	 *
	 * @ORM\Column(type="string", nullable=true)
	 */
	protected $target;

	/**
	 * @var int|null
	 *
	 * @ORM\Column(type="integer", nullable=true)
	 */
	protected $targetKey;

	public function __construct(NotificationTypeEntity $type)
	{
		$this->type = $type;
		$this->created = new DateTime();
	}

	public function setType(NotificationTypeEntity $type)
	{
		$this->type = $type;
	}

	public function setUser(UserEntity $user = null)
	{
		$this->user = $user;
	}

}

