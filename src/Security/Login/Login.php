<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Security\Login;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Venne\Doctrine\Entities\BaseEntity;
use Venne\Security\User\User;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 *
 * @ORM\Entity
 * @ORM\Table(name="login",indexes={@ORM\Index(name="created_idx", columns={"created"})})
 */
class Login extends BaseEntity
{

	/**
	 * @var string
	 *
	 * @ORM\Id
	 * @ORM\Column(type="string")
	 */
	private $sessionId;

	/**
	 * @var \Venne\Security\User\User
	 *
	 * @ORM\ManyToOne(targetEntity="\Venne\Security\User\User", inversedBy="logins")
	 * @ORM\JoinColumn(referencedColumnName="id", onDelete="CASCADE")
	 */
	private $user;

	/**
	 * @var bool
	 *
	 * @ORM\Column(type="boolean")
	 */
	private $reload;

	/**
	 * @var \DateTime
	 *
	 * @ORM\Column(type="datetime")
	 */
	private $created;

	/**
	 * @param string $sessionId
	 * @param \Venne\Security\User\User $user
	 */
	public function __construct($sessionId, User $user = null)
	{
		$this->sessionId = (string) $sessionId;
		$this->user = $user;
		$this->created = new DateTime();
		$this->reload = false;
	}

	/**
	 * @return string
	 */
	public function getSessionId()
	{
		return $this->sessionId;
	}

	/**
	 * @return \Venne\Security\User\User
	 */
	public function getUser()
	{
		return $this->user;
	}

	/**
	 * @return \DateTime
	 */
	public function getCreated()
	{
		return $this->created;
	}

}
