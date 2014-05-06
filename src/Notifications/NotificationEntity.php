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
use Venne\Doctrine\Entities\IdentifiedEntityTrait;
use Venne\Security\UserEntity;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 * @ORM\Entity
 * @ORM\Table(name="notification", indexes={@ORM\Index(name="created_idx", columns={"created"})})
 *
 * @property NotificationTypeEntity $type
 * @property UserEntity $user
 * @property \DateTime $created
 * @property string $target
 * @property int $targetKey
 */
class NotificationEntity extends BaseEntity
{

	use IdentifiedEntityTrait;

	/**
	 * @var NotificationTypeEntity
	 * @ORM\ManyToOne(targetEntity="NotificationTypeEntity")
	 * @ORM\JoinColumn(onDelete="CASCADE")
	 */
	protected $type;

	/**
	 * @var UserEntity
	 * @ORM\ManyToOne(targetEntity="\Venne\Security\UserEntity")
	 * @ORM\JoinColumn(onDelete="CASCADE")
	 */
	protected $user;

	/**
	 * @var \DateTime
	 * @ORM\Column(type="datetime")
	 */
	protected $created;

	/**
	 * @var string
	 * @ORM\Column(type="string", nullable=true)
	 */
	protected $target;

	/**
	 * @var int
	 * @ORM\Column(type="integer", nullable=true)
	 */
	protected $targetKey;


	/**
	 * @param NotificationTypeEntity $type
	 */
	public function __construct(NotificationTypeEntity $type)
	{
		$this->type = $type;
		$this->created = new \DateTime;
	}


	/**
	 * @param NotificationTypeEntity $type
	 */
	public function setType(NotificationTypeEntity $type)
	{
		$this->type = $type;
	}


	/**
	 * @param UserEntity $user
	 */
	public function setUser(UserEntity $user = NULL)
	{
		$this->user = $user;
	}

}

