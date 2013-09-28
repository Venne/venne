<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace CmsModule\Pages\Users;

use CmsModule\Content\Entities\ExtendedPageEntity;
use Doctrine\ORM\Mapping as ORM;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 * @ORM\Entity(repositoryClass="\CmsModule\Content\Repositories\PageRepository")
 * @ORM\Table(name="user_page")
 */
class PageEntity extends ExtendedPageEntity
{

	/**
	 * @var string
	 * @ORM\Column(type="integer")
	 */
	protected $itemsPerPage = 10;

	/**
	 * @var \Doctrine\Common\Collections\ArrayCollection|\CmsModule\Security\Entities\RoleEntity[]
	 * @ORM\ManyToMany(targetEntity="\CmsModule\Security\Entities\RoleEntity")
	 * @ORM\JoinTable(name="user_page_roles",
	 *      joinColumns={@ORM\JoinColumn(name="page_id", referencedColumnName="id", onDelete="CASCADE")},
	 *      inverseJoinColumns={@ORM\JoinColumn(name="role_id", referencedColumnName="id", onDelete="CASCADE")}
	 *      )
	 */
	protected $roles;


	/**
	 * @return string
	 */
	public static function getMainRouteName()
	{
		return 'CmsModule\Pages\Users\UsersEntity';
	}


	protected function getSpecial()
	{
		return 'users';
	}


	/**
	 * @param string $itemsPerPage
	 */
	public function setItemsPerPage($itemsPerPage)
	{
		$this->itemsPerPage = $itemsPerPage;
	}


	/**
	 * @return string
	 */
	public function getItemsPerPage()
	{
		return $this->itemsPerPage;
	}


	/**
	 * @param \CmsModule\Security\Entities\RoleEntity[]|\Doctrine\Common\Collections\ArrayCollection $roles
	 */
	public function setRoles($roles)
	{
		$this->roles = $roles;
	}


	/**
	 * @return \CmsModule\Security\Entities\RoleEntity[]|\Doctrine\Common\Collections\ArrayCollection
	 */
	public function getRoles()
	{
		return $this->roles;
	}
}
