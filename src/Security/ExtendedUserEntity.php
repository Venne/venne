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

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 *
 * @ORM\MappedSuperclass
 */
abstract class ExtendedUserEntity extends \Kdyby\Doctrine\Entities\BaseEntity
{

	/**
	 * @var \Venne\Security\UserEntity
	 *
	 * @ORM\Id
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

	/**
	 * @return string
	 */
	public function __toString()
	{
		return $this->user->__toString();
	}

	/**
	 * @return \Venne\Security\UserEntity
	 */
	public function getUser()
	{
		return $this->user;
	}

	/**
	 * @return \Venne\Security\UserEntity
	 */
	private function createUserEntity()
	{
		return new UserEntity;
	}

}
