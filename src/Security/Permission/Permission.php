<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Security\Permission;

use Doctrine\ORM\Mapping as ORM;
use Venne\Doctrine\Entities\BaseEntity;
use Venne\Security\Role\Role;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 *
 * @ORM\Entity
 * @ORM\Table(name="permission")
 */
class Permission extends \Venne\Doctrine\Entities\BaseEntity
{

	use \Venne\Doctrine\Entities\IdentifiedEntityTrait;

	/**
	 * @var string|null
	 *
	 * @ORM\Column(type="string", nullable=true)
	 */
	protected $resource;

	/**
	 * @var string|null
	 *
	 * @ORM\Column(type="string", nullable=true)
	 */
	protected $privilege;

	/**
	 * @var bool
	 *
	 * @ORM\Column(type="boolean")
	 */
	protected $allow;

	/**
	 * @var \Venne\Security\Role\Role
	 *
	 * @ORM\ManyToOne(targetEntity="\Venne\Security\Role\Role", inversedBy="permissions")
	 * @ORM\JoinColumn(name="role_id", referencedColumnName="id", onDelete="CASCADE")
	 */
	private $role;

	/**
	 * @param \Venne\Security\Role\Role $role
	 * @param string $resource
	 * @param string $privilege
	 * @param bool $allow
	 */
	public function __construct(Role $role, $resource, $privilege = null, $allow = true)
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
	 * @return \Venne\Security\Role\Role
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
	 * @return bool
	 */
	public function getAllow()
	{
		return $this->allow;
	}

	/**
	 * @param bool $allow
	 */
	public function setAllow($allow)
	{
		$this->allow = (bool) $allow;
	}

}
