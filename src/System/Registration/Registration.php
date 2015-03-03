<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\System\Registration;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Venne\Security\Role\Role;
use Venne\Security\UserType;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 *
 * @ORM\Entity
 * @ORM\Table(name="registrations")
 */
class Registration extends \Venne\Doctrine\Entities\BaseEntity
{

	use \Venne\Doctrine\Entities\NamedEntityTrait;

	/**
	 * @var bool
	 *
	 * @ORM\Column(type="boolean")
	 */
	private $enabled = false;

	/**
	 * @var bool
	 *
	 * @ORM\Column(type="boolean")
	 */
	private $invitation = false;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="string")
	 */
	private $userType;

	/**
	 * @var \Venne\System\Registration\RegistrationMode
	 *
	 * @ORM\Column(type="Venne\System\Registration\RegistrationMode")
	 */
	private $mode;

	/**
	 * @var \Venne\System\Registration\LoginProviderMode
	 *
	 * @ORM\Column(type="Venne\System\Registration\LoginProviderMode")
	 */
	private $loginProviderMode;

	/**
	 * @var \Venne\Security\Role\Role[]|\Doctrine\Common\Collections\ArrayCollection
	 *
	 * @ORM\ManyToMany(targetEntity="\Venne\Security\Role\Role")
	 */
	private $roles;

	public function __construct($name, UserType $userType, RegistrationMode $mode, LoginProviderMode $loginProviderMode)
	{
		$this->setName($name);
		$this->setMode($mode);
		$this->setLoginProviderMode($loginProviderMode);
		$this->setUserType($userType);

		$this->roles = new ArrayCollection();
	}

	/**
	 * @param bool $enabled
	 */
	public function setEnabled($enabled)
	{
		$this->enabled = (bool) $enabled;
	}

	/**
	 * @return bool
	 */
	public function isEnabled()
	{
		return $this->enabled;
	}

	/**
	 * @param bool $invitation
	 */
	public function setInvitation($invitation)
	{
		$this->invitation = (bool) $invitation;
	}

	/**
	 * @return bool
	 */
	public function isInvitation()
	{
		return $this->invitation;
	}

	public function setUserType(UserType $userType)
	{
		$this->userType = $userType->getEntityName();
	}

	/**
	 * @return string
	 */
	public function getUserTypeName()
	{
		return $this->userType;
	}

	public function setMode(RegistrationMode $mode)
	{
		$this->mode = $mode;
	}

	/**
	 * @return \Venne\System\Registration\RegistrationMode
	 */
	public function getMode()
	{
		return $this->mode;
	}

	public function setLoginProviderMode(LoginProviderMode $loginProviderMode)
	{
		$this->loginProviderMode = $loginProviderMode;
	}

	/**
	 * @return \Venne\System\Registration\LoginProviderMode
	 */
	public function getLoginProviderMode()
	{
		return $this->loginProviderMode;
	}

	public function addRole(Role $role)
	{
		$this->roles[] = $role;
	}

	public function removeRole(Role $role)
	{
		$this->roles->removeElement($role);
	}

	/**
	 * @return \Venne\Security\Role\Role[]
	 */
	public function getRoles()
	{
		return $this->roles->toArray();
	}

}
