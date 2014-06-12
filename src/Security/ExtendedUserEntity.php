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
 *
 * @ORM\MappedSuperclass
 */
abstract class ExtendedUserEntity extends BaseEntity
{

	use IdentifiedEntityTrait;

	/**
	 * @var UserEntity
	 * @ORM\OneToOne(targetEntity="\Venne\Security\UserEntity", cascade={"all"})
	 * @ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE")
	 */
	protected $user;


	public function __construct()
	{
		$this->user = $this->createUserEntity();
		$this->user->setClass(get_class($this));
		$this->startup();
	}


	public function startup()
	{
	}


	public function __toString()
	{
		return $this->user->__toString();
	}


	/**
	 * @return UserEntity
	 */
	public function getUser()
	{
		return $this->user;
	}


	/**
	 * @return UserEntity
	 */
	private function createUserEntity()
	{
		return new UserEntity;
	}

}
