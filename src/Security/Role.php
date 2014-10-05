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

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Kdyby\Doctrine\Entities\BaseEntity;
use Nette\InvalidArgumentException;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 *
 * @ORM\Entity
 * @ORM\Table(name="role")
 */
class Role extends \Kdyby\Doctrine\Entities\BaseEntity
{

	use \Venne\Doctrine\Entities\IdentifiedEntityTrait;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="string", unique=true, length=32)
	 */
	private $name;

	/**
	 * @var \Venne\Security\Role[]|\Doctrine\Common\Collections\ArrayCollection
	 *
	 * @ORM\OneToMany(targetEntity="\Venne\Security\Role", mappedBy="parent")
	 */
	private $children;

	/**
	 * @var \Venne\Security\Role|null
	 *
	 * @ORM\ManyToOne(targetEntity="\Venne\Security\Role", inversedBy="children")
	 * @ORM\JoinColumn(name="role_id", referencedColumnName="id", onDelete="CASCADE")
	 */
	private $parent;

	/**
	 * @var \Venne\Security\Permission[]|\Doctrine\Common\Collections\ArrayCollection
	 *
	 * @ORM\OneToMany(targetEntity="\Venne\Security\Permission", mappedBy="role", cascade={"persist"}, orphanRemoval=true)
	 */
	private $permissions;

	/**
	 * @var \Venne\Security\User[]|\Doctrine\Common\Collections\ArrayCollection
	 *
	 * @ORM\ManyToMany(targetEntity="\Venne\Security\User", mappedBy="roleEntities")
	 */
	private $users;

	/**
	 * @return string
	 */
	public function __toString()
	{
		return (string) $this->name;
	}

	public function __construct()
	{
		$this->permissions = new ArrayCollection();
		$this->users = new ArrayCollection();
		$this->children = new ArrayCollection();
	}

	/**
	 * @return Role[]
	 */
	public function getChildren()
	{
		return $this->children->toArray();
	}

	/**
	 * @param Role $child
	 */
	public function addChildren(Role $child)
	{
		$this->children[] = $child;
	}

	/**
	 * @param Role $child
	 */
	public function removeChildren(Role $child)
	{
		$this->children->removeElement($child);
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
		$name = (string) $name;

		if ($name === '') {
			throw new InvalidArgumentException('Name can not be blank.');
		}

		$this->name = $name;
	}

	/**
	 * @return null|Role
	 */
	public function getParent()
	{
		return $this->parent;
	}

	/**
	 * @param Role|null $parent
	 */
	public function setParent(Role $parent = null)
	{
		$this->parent = $parent;
	}

	/**
	 * @return \Venne\Security\Permission[]
	 */
	public function getPermissions()
	{
		return $this->permissions->toArray();
	}

	/**
	 * @param \Venne\Security\Permission $permission
	 */
	public function addPermission(Permission $permission)
	{
		$this->permissions[] = $permission;
	}

	/**
	 * @param \Venne\Security\Permission $permission
	 */
	public function removePermission(Permission $permission)
	{
		$this->permissions->removeElement($permission);
	}

	/**
	 * @return \Venne\Security\User[]
	 */
	public function getUsers()
	{
		return $this->users->toArray();
	}

	/**
	 * @param \Venne\Security\User $user
	 */
	public function addUser(User $user)
	{
		$this->users[] = $user;
	}

	/**
	 * @param \Venne\Security\User $user
	 */
	public function removeUser(User $user)
	{
		$this->users->removeElement($user);
	}

}
