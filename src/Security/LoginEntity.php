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

use Doctrine\ORM\Mapping as ORM;
use Kdyby\Doctrine\Entities\BaseEntity;
use Venne\Doctrine\Entities\IdentifiedEntityTrait;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 * @ORM\Entity
 * @ORM\Table(name="login",indexes={@ORM\Index(name="session_idx", columns={"session_id"})})
 */
class LoginEntity extends BaseEntity
{

	use IdentifiedEntityTrait;

	const USER_ADMIN = NULL;

	/**
	 * @ORM\ManyToOne(targetEntity="UserEntity", inversedBy="logins")
	 * @ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE")
	 */
	protected $user;

	/** @ORM\Column(name="session_id", type="string", nullable=true) */
	protected $sessionId;

	/** @ORM\Column(type="boolean") */
	protected $reload;

	/** @ORM\Column(type="datetime") */
	protected $created;


	/**
	 * @param $user
	 * @param $sessionId
	 */
	public function __construct($sessionId, $user = NULL)
	{
		$this->user = $user;
		$this->sessionId = $sessionId;
		$this->created = new \DateTime;
		$this->reload = false;
	}


	/**
	 * @param $sessionId
	 */
	public function setSessionId($sessionId)
	{
		$this->sessionId = $sessionId;
	}


	/**
	 * @return mixed
	 */
	public function getSessionId()
	{
		return $this->sessionId;
	}


	/**
	 * @param $user
	 */
	public function setUser($user)
	{
		$this->user = $user;
	}


	/**
	 * @return mixed
	 */
	public function getUser()
	{
		return $this->user;
	}


	public function setCreated($created)
	{
		$this->created = $created;
	}


	public function getCreated()
	{
		return $this->created;
	}


	public function setReload($reload)
	{
		$this->reload = $reload;
	}


	public function getReload()
	{
		return $this->reload;
	}
}
