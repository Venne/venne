<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace CmsModule\Security\Entities;

use Venne;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 * @Entity(repositoryClass="\DoctrineModule\ORM\BaseRepository")
 * @Table(name="login",indexes={@index(name="search_idx", columns={"sessionId"})})
 */
class LoginEntity extends \DoctrineModule\ORM\BaseEntity
{


	const USER_ADMIN = NULL;

	/**
	 * @ManyToOne(targetEntity="UserEntity", inversedBy="id")
	 * @JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE")
	 */
	protected $user;

	/** @Column(type="string", nullable=true) */
	protected $sessionId;

	/** @Column(type="boolean") */
	protected $reload;

	/** @Column(type="datetime") */
	protected $created;



	/**
	 * @param $user
	 * @param $sessionId
	 */
	public function __construct($user, $sessionId)
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
