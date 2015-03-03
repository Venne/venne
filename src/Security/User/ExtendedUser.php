<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Security\User;

use Doctrine\ORM\Mapping as ORM;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 *
 * @ORM\MappedSuperclass
 */
abstract class ExtendedUser extends \Venne\Doctrine\Entities\BaseEntity
{

	/**
	 * @var \Venne\Security\User\User
	 *
	 * @ORM\Id
	 * @ORM\OneToOne(targetEntity="\Venne\Security\User\User", cascade={"all"})
	 * @ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE")
	 */
	private $user;

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
	 * @return \Venne\Security\User\User
	 */
	public function getUser()
	{
		return $this->user;
	}

	/**
	 * @return \Venne\Security\User\User
	 */
	private function createUserEntity()
	{
		return new User();
	}

}
