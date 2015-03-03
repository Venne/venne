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
use Venne\Security\User\User;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 *
 * @property \Venne\Notifications\NotificationType $type
 * @property \Venne\Security\User\User $user
 * @property \DateTime $created
 * @property string $target
 * @property int $targetKey
 *
 * @ORM\Entity
 * @ORM\Table(name="notification", indexes={@ORM\Index(name="created_idx", columns={"created"})})
 */
class Notification extends \Venne\Doctrine\Entities\BaseEntity
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
	 * @var \Venne\Security\User\User
	 *
	 * @ORM\ManyToOne(targetEntity="\Venne\Security\User\User")
	 * @ORM\JoinColumn(onDelete="CASCADE")
	 */
	private $user;

	/**
	 * @var \DateTime
	 *
	 * @ORM\Column(type="datetime")
	 */
	private $created;

	/**
	 * @var string|null
	 *
	 * @ORM\Column(type="string", nullable=true)
	 */
	private $target;

	/**
	 * @var integer|null
	 *
	 * @ORM\Column(type="integer", nullable=true)
	 */
	private $targetKey;

	/**
	 * @param \Venne\Notifications\NotificationType $type
	 * @param \Venne\Security\User\User $user
	 * @param string|null $target
	 * @param int|null $targetKey
	 */
	public function __construct(NotificationType $type, User $user, $target = null, $targetKey = null)
	{
		$this->type = $type;
		$this->user = $user;
		$this->target = $target;
		$this->targetKey = $targetKey;
		$this->created = new DateTime();
	}

	/**
	 * @return \DateTime
	 */
	public function getCreated()
	{
		return $this->created;
	}

	/**
	 * @return string
	 */
	public function getTarget()
	{
		return $this->target;
	}

	/**
	 * @return int
	 */
	public function getTargetKey()
	{
		return $this->targetKey;
	}

	/**
	 * @return \Venne\Notifications\NotificationType
	 */
	public function getType()
	{
		return $this->type;
	}

	/**
	 * @return \Venne\Security\User\User
	 */
	public function getUser()
	{
		return $this->user;
	}

}
