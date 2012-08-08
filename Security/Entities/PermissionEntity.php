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
class PermissionEntity extends \DoctrineModule\ORM\BaseEntity {


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
	 * @ManyToOne(targetEntity="RoleEntity", inversedBy="id")
	 * @JoinColumn(name="role_id", referencedColumnName="id", onDelete="CASCADE")
	 */
	protected $role;



	/**
	 * @return string
	 */
	public function getResource()
	{
		return $this->resource;
	}



	/**
	 * @param string $resource
	 */
	public function setResource($resource)
	{
		$this->resource = $resource;
	}



	/**
	 * @return string
	 */
	public function getRole()
	{
		return $this->role;
	}



	/**
	 * @param string $role
	 */
	public function setRole($role)
	{
		$this->role = $role;

		foreach($this->role->users as $user){
			$user->invalidateLogins();
		}
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



	public function getPrivilege()
	{
		return $this->privilege;
	}



	public function setPrivilege($privilege)
	{
		$this->privilege = $privilege;
	}

}
