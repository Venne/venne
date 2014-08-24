<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Security;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Kdyby\Doctrine\Entities\BaseEntity;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 *
 * @ORM\Entity
 * @ORM\Table(name="login",indexes={@ORM\Index(name="session_idx", columns={"session_id"})})
 */
class LoginEntity extends \Kdyby\Doctrine\Entities\BaseEntity
{

	use \Venne\Doctrine\Entities\IdentifiedEntityTrait;

	const USER_ADMIN = null;

	/**
	 * @var \Venne\Security\UserEntity
	 *
	 * @ORM\ManyToOne(targetEntity="\Venne\Security\UserEntity", inversedBy="logins")
	 * @ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE")
	 */
	protected $user;

	/**
	 * @var string|null
	 *
	 * @ORM\Column(name="session_id", type="string", nullable=true)
	 */
	protected $sessionId;

	/**
	 * @var bool
	 *
	 * @ORM\Column(type="boolean")
	 */
	protected $reload;

	/**
	 * @var \DateTime
	 *
	 * @ORM\Column(type="datetime")
	 */
	protected $created;

	/**
	 * @param string $sessionId
	 * @param \Venne\Security\UserEntity $user
	 */
	public function __construct($sessionId, UserEntity $user = null)
	{
		$this->setUser($user);
		$this->setSessionId($sessionId);
		$this->created = new DateTime();
		$this->reload = false;
	}

	/**
	 * @param string $sessionId
	 */
	public function setSessionId($sessionId)
	{
		$this->sessionId = $sessionId;
	}

	/**
	 * @return string
	 */
	public function getSessionId()
	{
		return $this->sessionId;
	}

	public function setUser(UserEntity $user)
	{
		$this->user = $user;
	}

	/**
	 * @return \Venne\Security\UserEntity
	 */
	public function getUser()
	{
		return $this->user;
	}

	public function setCreated(DateTime $created)
	{
		$this->created = $created;
	}

	/**
	 * @return \DateTime
	 */
	public function getCreated()
	{
		return $this->created;
	}

	/**
	 * @param bool $reload
	 */
	public function setReload($reload)
	{
		$this->reload = $reload;
	}

	/**
	 * @return bool
	 */
	public function getReload()
	{
		return $this->reload;
	}

}
