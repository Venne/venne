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

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 *
 * @ORM\Entity
 * @ORM\Table(name="role")
 */
class RoleEntity extends \Kdyby\Doctrine\Entities\BaseEntity
{

	use \Venne\Doctrine\Entities\IdentifiedEntityTrait;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="string", unique=true, length=32)
	 */
	protected $name;

	/**
	 * @var \Venne\Security\RoleEntity[]|\Doctrine\Common\Collections\ArrayCollection
	 *
	 * @ORM\OneToMany(targetEntity="\Venne\Security\RoleEntity", mappedBy="parent")
	 */
	protected $children;

	/**
	 * @var \Venne\Security\RoleEntity|null
	 *
	 * @ORM\ManyToOne(targetEntity="\Venne\Security\RoleEntity", inversedBy="children")
	 * @ORM\JoinColumn(name="role_id", referencedColumnName="id", onDelete="CASCADE")
	 */
	protected $parent;

	/**
	 * @var \Venne\Security\PermissionEntity[]|\Doctrine\Common\Collections\ArrayCollection
	 *
	 * @ORM\OneToMany(targetEntity="\Venne\Security\PermissionEntity", mappedBy="role", cascade={"persist"}, orphanRemoval=true)
	 */
	protected $permissions;

	/**
	 * @var \Venne\Security\UserEntity[]|\Doctrine\Common\Collections\ArrayCollection
	 *
	 * @ORM\ManyToMany(targetEntity="\Venne\Security\UserEntity", mappedBy="roleEntities")
	 */
	protected $users;

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

}
