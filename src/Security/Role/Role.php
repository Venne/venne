<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Security\Role;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Nette\InvalidArgumentException;
use Venne\Security\Permission\Permission;
use Venne\Security\User\User;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 *
 * @ORM\Entity
 * @ORM\Table(name="role")
 */
class Role extends \Venne\Doctrine\Entities\BaseEntity
{

	use \Venne\Doctrine\Entities\IdentifiedEntityTrait;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="string", unique=true, length=32)
	 */
	private $name;

	/**
	 * @var \Venne\Security\Role\Role[]|\Doctrine\Common\Collections\ArrayCollection
	 *
	 * @ORM\OneToMany(targetEntity="\Venne\Security\Role\Role", mappedBy="parent")
	 */
	private $children;

	/**
	 * @var \Venne\Security\Role\Role|null
	 *
	 * @ORM\ManyToOne(targetEntity="\Venne\Security\Role\Role", inversedBy="children")
	 * @ORM\JoinColumn(name="role_id", referencedColumnName="id", onDelete="CASCADE")
	 */
	private $parent;

	/**
	 * @var \Venne\Security\Permission\Permission[]|\Doctrine\Common\Collections\ArrayCollection
	 *
	 * @ORM\OneToMany(targetEntity="\Venne\Security\Permission\Permission", mappedBy="role", cascade={"persist"}, orphanRemoval=true)
	 */
	private $permissions;

	/**
	 * @var \Venne\Security\User\User[]|\Doctrine\Common\Collections\ArrayCollection
	 *
	 * @ORM\ManyToMany(targetEntity="\Venne\Security\User\User", mappedBy="roleEntities")
	 */
	private $users;

	/**
	 * @return string
	 */
	public function __toString()
	{
		return $this->getName();
	}

	/**
	 * @param string $name
	 */
	public function __construct($name)
	{
		$this->setName($name);

		$this->permissions = new ArrayCollection();
		$this->users = new ArrayCollection();
		$this->children = new ArrayCollection();
	}

	/**
	 * @return \Venne\Security\Role\Role[]
	 */
	public function getChildren()
	{
		return $this->children->toArray();
	}

	public function addChildren(Role $child)
	{
		$this->doTransaction(function () use ($child) {
			$this->children->add($child);
			$child->setParent($this);
		}, __METHOD__);
	}

	public function removeChildren(Role $child)
	{
		$this->doTransaction(function () use ($child) {
			$this->children->removeElement($child);
			$child->setParent(null);
		}, __METHOD__);
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
	 * @return \Venne\Security\Role\Role|null
	 */
	public function getParent()
	{
		return $this->parent;
	}

	public function setParent(Role $parent = null)
	{
		$this->doTransaction(function () use ($parent) {
			if ($this->parent !== null) {
				$this->parent->removeChildren($this);
			}

			$this->parent = $parent;

			if ($this->parent !== null) {
				$this->parent->addChildren($this);
			}
		}, __METHOD__);
	}

	/**
	 * @return \Venne\Security\Permission\Permission[]
	 */
	public function getPermissions()
	{
		return $this->permissions->toArray();
	}

	public function addPermission(Permission $permission)
	{
		$this->permissions->add($permission);
	}

	public function removePermission(Permission $permission)
	{
		$this->permissions->removeElement($permission);
	}

	/**
	 * @return \Venne\Security\User\User[]
	 */
	public function getUsers()
	{
		return $this->users->toArray();
	}

	public function addUser(User $user)
	{
		$this->doTransaction(function () use ($user) {
			$this->users->add($user);
			$user->addRoleEntity($this);
		}, __METHOD__);
	}

	public function removeUser(User $user)
	{
		$this->doTransaction(function () use ($user) {
			$this->users->removeElement($user);
			$user->removeRoleEntity($this);
		}, __METHOD__);
	}

}
