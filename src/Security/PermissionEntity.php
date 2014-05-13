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
use Venne\Doctrine\Entities\IdentifiedEntityTrait;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 * @ORM\Entity
 * @ORM\Table(name="permission")
 */
class PermissionEntity extends BaseEntity
{

	use IdentifiedEntityTrait;

	/**
	 * @ORM\Column(type="string", nullable=true)
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
	 * @param null $privilege
	 * @param bool $allow
	 */
	public function __construct(RoleEntity $role, $resource, $privilege = NULL, $allow = true)
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
