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
 * @Entity(repositoryClass="\DoctrineModule\Repositories\BaseRepository")
 * @Table(name="socialLogin",
 *     uniqueConstraints={@UniqueConstraint(name="uniqueKey", columns={"type", "uniqueKey"})}
 * )
 */
class SocialLoginEntity extends \DoctrineModule\Entities\IdentifiedEntity
{


	/**
	 * @ManyToOne(targetEntity="UserEntity", inversedBy="socialLogins")
	 * @JoinColumn(onDelete="CASCADE")
	 */
	protected $user;

	/** @Column(type="string") */
	protected $type;

	/** @Column(type="string") */
	protected $uniqueKey;

	/** @Column(type="text", nullable=true) */
	protected $data;


	/**
	 * @param $user
	 * @param $sessionId
	 */
	public function setUser(UserEntity $user)
	{
		$this->user = $user;
	}


	public function setType($type)
	{
		$this->type = $type;
	}


	public function getType()
	{
		return $this->type;
	}


	public function setUniqueKey($uniqueKey)
	{
		$this->uniqueKey = $uniqueKey;
	}


	public function getUniqueKey()
	{
		return $this->uniqueKey;
	}


	public function setData($data)
	{
		$this->data = json_encode($data);
	}


	public function getData()
	{
		return json_decode($this->data);
	}
}
