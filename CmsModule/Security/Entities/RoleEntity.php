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

use Doctrine\ORM\Mapping as ORM;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 * @ORM\Entity(repositoryClass="\CmsModule\Security\Repositories\RoleRepository")
 * @ORM\Table(name="role")
 */
class RoleEntity extends \DoctrineModule\Entities\IdentifiedEntity
{

	/**
	 * @ORM\Column(type="string", unique=true, length=32)
	 */
	protected $name;

	/**
	 * @ORM\OneToMany(targetEntity="RoleEntity", mappedBy="parent")
	 */
	protected $children;

	/**
	 * @ORM\ManyToOne(targetEntity="RoleEntity", inversedBy="children")
	 * @ORM\JoinColumn(name="role_id", referencedColumnName="id", onDelete="CASCADE")
	 */
	protected $parent;

	/**
	 * @ORM\OneToMany(targetEntity="PermissionEntity", mappedBy="role", cascade={"persist"}, orphanRemoval=true)
	 */
	protected $permissions;

	/**
	 * @ORM\ManyToMany(targetEntity="UserEntity", mappedBy="roleEntities")
	 */
	protected $users;


	public function __toString()
	{
		return $this->name;
	}


	public function __construct()
	{
		$this->permissions = new \Doctrine\Common\Collections\ArrayCollection();
		$this->users = new \Doctrine\Common\Collections\ArrayCollection();
		$this->children = new \Doctrine\Common\Collections\ArrayCollection();
	}


	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}


	/**
	 * @param string $name
	 */
	public function setName($name)
	{
		$this->name = $name;
	}


	/**
	 * @return RoleEntity
	 */
	public function getParent()
	{
		return $this->parent;
	}


	/**
	 * @param RoleEntity $parent
	 */
	public function setParent($parent)
	{
		$this->parent = $parent;
	}


	/**
	 * @return \Doctrine\Common\Collections\ArrayCollection
	 */
	public function getChildren()
	{
		return $this->children;
	}


	/**
	 * @param RoleEntity $children
	 */
	public function addChildren($children)
	{
		$this->children[] = $children;
	}


	/**
	 * @return \Doctrine\Common\Collections\ArrayCollection|PermissionEntity[]
	 */
	public function getPermissions()
	{
		return $this->permissions;
	}


	/**
	 * @param $users
	 */
	public function setUsers($users)
	{
		$this->users = $users;
	}


	/**
	 * @return UserEntity[]
	 */
	public function getUsers()
	{
		return $this->users;
	}
}
