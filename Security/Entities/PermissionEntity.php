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
 * @Table(name="permission")
 */
class PermissionEntity extends \DoctrineModule\ORM\BaseEntity
{


	/**
	 * @Column(type="string")
	 */
	protected $resource;

	/**
	 * @Column(type="string", nullable=true)
	 */
	protected $privilege;

	/**
	 * @Column(type="boolean")
	 */
	protected $allow;

	/**
	 * @var RoleEntity
	 * @ManyToOne(targetEntity="RoleEntity", inversedBy="id")
	 * @JoinColumn(name="role_id", referencedColumnName="id", onDelete="CASCADE")
	 */
	protected $role;


	/**
	 * @param RoleEntity $role
	 * @param $resource
	 */
	function __construct(RoleEntity $role, $resource, $privilege = NULL, $allow = true)
	{
		$this->role = $role;
		$this->resource = $resource;
		$this->privilege = $privilege;
		$this->allow = $allow;
	}


	/**
	 * @return string
	 */
	public function getResource()
	{
		return $this->resource;
	}


	/**
	 * @return RoleEntity
	 */
	public function getRole()
	{
		return $this->role;
	}


	/**
	 * @return string
	 */
	public function getPrivilege()
	{
		return $this->privilege;
	}


	/**
	 * @return string
	 */
	public function getAllow()
	{
		return $this->allow;
	}


	/**
	 * @param string $allow
	 */
	public function setAllow($allow)
	{
		$this->allow = $allow;
	}
}
