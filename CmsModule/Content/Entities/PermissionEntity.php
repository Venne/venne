<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace CmsModule\Content\Entities;

use CmsModule\Security\Entities\RoleEntity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use DoctrineModule\Entities\NamedEntity;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 * @ORM\Entity
 * @ORM\Table(name="pagePermission")
 *
 * @property RoleEntity[] $roles
 * @property PageEntity $page;
 * @property bool $all
 */
class PermissionEntity extends NamedEntity
{

	const CACHE = 'Cms.PermissionEntity';

	/**
	 * @var RoleEntity[]
	 * @ORM\ManyToMany(targetEntity="\CmsModule\Security\Entities\RoleEntity", indexBy="name")
	 * @ORM\JoinTable(name="pagePermission_roles",
	 *      joinColumns={@ORM\JoinColumn(name="pagePermission_id", referencedColumnName="id", onDelete="CASCADE")},
	 *      inverseJoinColumns={@ORM\JoinColumn(name="role_id", referencedColumnName="id", onDelete="CASCADE")}
	 *      )
	 **/
	protected $roles;

	/**
	 * @var bool
	 * @ORM\Column(type="boolean", name="allowAll")
	 */
	protected $all = TRUE;

	/**
	 * @var PageEntity
	 * @ORM\ManyToOne(targetEntity="\CmsModule\Content\Entities\PageEntity", inversedBy="permissions")
	 * @ORM\JoinColumn(name="page_id", referencedColumnName="id", onDelete="CASCADE")
	 */
	protected $page;


	public function __construct()
	{
		$this->roles = new ArrayCollection;
	}


	/**
	 * @param PageEntity $page
	 */
	public function setPage(PageEntity $page)
	{
		$this->page = $page;
	}


	/**
	 * @return PageEntity
	 */
	public function getPage()
	{
		return $this->page;
	}


	/**
	 * @param RoleEntity[] $roles
	 */
	public function setRoles($roles)
	{
		$this->roles = $roles;
	}


	/**
	 * @return RoleEntity[]
	 */
	public function getRoles()
	{
		return $this->roles;
	}


	/**
	 * @param bool $all
	 */
	public function setAll($all)
	{
		$this->all = (bool)$all;
	}


	/**
	 * @return bool
	 */
	public function getAll()
	{
		return $this->all;
	}
}

