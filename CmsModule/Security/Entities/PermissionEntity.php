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
use Doctrine\ORM\Mapping as ORM;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 * @ORM\Entity(repositoryClass="\CmsModule\Security\Repositories\PermissionRepository")
 * @ORM\Table(name="permission")
 */
class PermissionEntity extends \DoctrineModule\Entities\IdentifiedEntity
{


	/**
	 * @ORM\Column(type="string")
	 */
	protected $resource;

	/**
	 * @ORM\Column(type="string", nullable=true)
	 */
	protected $privilege;

	/**
	 * @ORM\Column(type="boolean")
	 */
	protected $allow;

	/**
	 * @var RoleEntity
	 * @ORM\ManyToOne(targetEntity="RoleEntity", inversedBy="permissions")
	 * @ORM\JoinColumn(name="role_id", referencedColumnName="id", onDelete="CASCADE")
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
