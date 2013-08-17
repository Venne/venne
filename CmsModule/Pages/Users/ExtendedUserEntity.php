<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace CmsModule\Pages\Users;

use CmsModule\Content\Entities\ExtendedPageEntity;
use Doctrine\ORM\Mapping as ORM;
use DoctrineModule\Entities\IdentifiedEntity;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 *
 * @ORM\MappedSuperclass
 */
abstract class ExtendedUserEntity extends IdentifiedEntity
{

	/**
	 * @var UserEntity
	 * @ORM\OneToOne(targetEntity="\CmsModule\Pages\Users\UserEntity", cascade={"ALL"})
	 * @ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE")
	 */
	protected $user;


	/**
	 * @param ExtendedPageEntity $page
	 */
	public function __construct(ExtendedPageEntity $page)
	{
		$this->user = $this->createUserEntity($page);
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
	 * @param \CmsModule\Pages\Users\UserEntity $user
	 */
	public function setUser($user)
	{
		$this->user = $user;
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
	private function createUserEntity($page)
	{
		return new UserEntity($page);
	}
}
