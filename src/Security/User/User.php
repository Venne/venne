<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Security\User;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Nette\Security\Passwords;
use Nette\Utils\Callback;
use Nette\Utils\Random;
use Venne\Security\Login\Login;
use Venne\Security\LoginProvider;
use Venne\Security\Role\Role;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 *
 * @ORM\Entity
 * @ORM\EntityListeners({
 *        "\Venne\Security\User\ExtendedUserListener",
 *        "\Venne\Security\User\UserStateListener"
 * })
 * @ORM\Table(name="users")
 */
class User extends \Venne\Doctrine\Entities\BaseEntity implements \Nette\Security\IIdentity
{

	use \Venne\Doctrine\Entities\IdentifiedEntityTrait;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="string", unique=true, length=64)
	 */
	private $email;

	/**
	 * @var string|null
	 *
	 * @ORM\Column(type="string", nullable=true)
	 */
	private $name;

	/**
	 * @var string|null
	 *
	 * @ORM\Column(type="text", nullable=true)
	 */
	private $notation;

	/**
	 * @var string|null
	 *
	 * @ORM\Column(type="string", nullable=true)
	 */
	private $password;

	/**
	 * @var string|null
	 *
	 * @ORM\Column(type="string", name="enableByKey", nullable=true)
	 */
	private $key;

	/**
	 * @var bool
	 *
	 * @ORM\Column(type="boolean")
	 */
	private $published = true;

	/**
	 * @var \Venne\Security\Role\Role[]|\Doctrine\Common\Collections\ArrayCollection
	 *
	 * @ORM\ManyToMany(targetEntity="\Venne\Security\Role\Role", cascade={"persist"}, inversedBy="users")
	 * @ORM\JoinTable(name="users_roles",
	 *      joinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE")},
	 *      inverseJoinColumns={@ORM\JoinColumn(name="role_id", referencedColumnName="id", onDelete="CASCADE")}
	 *      )
	 */
	private $roleEntities;

	/**
	 * @var \Venne\Security\Login\Login[]|\Doctrine\Common\Collections\ArrayCollection
	 *
	 * @ORM\OneToMany(targetEntity="\Venne\Security\Login\Login", mappedBy="user")
	 */
	private $logins;

	/**
	 * @var \Venne\Security\LoginProvider[]|\Doctrine\Common\Collections\ArrayCollection
	 *
	 * @ORM\OneToMany(targetEntity="\Venne\Security\LoginProvider", mappedBy="user", cascade={"persist"}, orphanRemoval=true)
	 */
	private $loginProviders;

	/**
	 * @var \DateTime
	 *
	 * @ORM\Column(type="datetime")
	 */
	private $created;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="string")
	 */
	private $class;

	/**
	 * @var string|null
	 *
	 * @ORM\Column(type="string", nullable=true)
	 */
	private $resetKey;

	/** @var \Venne\Security\User\ExtendedUser */
	private $extendedUser;

	/** @var callable */
	private $extendedUserCallback;

	public function __construct()
	{
		$this->roleEntities = new ArrayCollection();
		$this->logins = new ArrayCollection();
		$this->loginProviders = new ArrayCollection();
		$this->created = new DateTime();
	}

	/**
	 * @param callable $extendedUserCallback
	 */
	public function setExtendedUserCallback($extendedUserCallback)
	{
		$this->extendedUserCallback = $extendedUserCallback;
	}

	/**
	 * @return \Venne\Security\User\ExtendedUser
	 */
	public function getExtendedUser()
	{
		if (!$this->extendedUser) {
			$this->extendedUser = Callback::invoke($this->extendedUserCallback);
		}

		return $this->extendedUser;
	}

	/**
	 * Invalidate all user logins.
	 */
	public function invalidateLogins()
	{
		foreach ($this->logins as $login) {
			$login->reload = true;
		}
	}

	/**
	 * @param string $password
	 */
	public function setPassword($password)
	{
		$this->password = Passwords::hash($password);
	}

	/**
	 * @return null
	 */
	public function getPassword()
	{
		return null;
	}

	/**
	 * @param string $password
	 * @return bool
	 */
	public function verifyByPassword($password)
	{
		if (!$this->isEnable() || $this->password === null) {
			return false;
		}

		return Passwords::verify($password, $this->password);
	}

	/**
	 * @return bool
	 */
	public function needsRehash()
	{
		return Passwords::needsRehash($this->password);
	}

	/**
	 * Disable user and verify by key.
	 */
	public function disableByKey()
	{
		$this->generateNewKey();
	}

	/**
	 * @param string $key
	 * @return bool
	 */
	public function enableByKey($key)
	{
		if ($this->key === $key) {
			$this->key = null;

			return true;
		}

		return false;
	}

	/**
	 * @return string
	 */
	public function resetPassword()
	{
		return $this->resetKey = Random::generate(30);
	}

	/**
	 * @param string $key
	 * @return bool
	 */
	public function removeResetKey($key)
	{
		if ($this->resetKey === $key) {
			$this->resetKey = null;

			return true;
		}

		return false;
	}

	/**
	 * @return null|string
	 */
	public function getResetKey()
	{
		return $this->resetKey;
	}

	/**
	 * Check if user is enable.
	 *
	 * @return bool
	 */
	public function isEnable()
	{
		if (!$this->key && $this->published) {
			return true;
		}

		return false;
	}

	/**
	 * @return string
	 */
	public function __toString()
	{
		return $this->name !== null ? $this->name : (string) $this->email;
	}

	/**
	 * @return ArrayCollection|Role[]
	 */
	public function getRoleEntities()
	{
		return $this->roleEntities->toArray();
	}

	public function addRoleEntity(Role $role)
	{
		$this->doTransaction(function () use ($role) {
			$this->roleEntities[] = $role;
			$role->addUser($this);
		});
	}

	/**
	 * @param \Venne\Security\Role\Role $role
	 * @return bool
	 */
	public function hasRoleEntity(Role $role)
	{
		return $this->roleEntities->contains($role);
	}

	public function removeRoleEntity(Role $role)
	{
		$this->doTransaction(function () use ($role) {
			$this->roleEntities->removeElement($role);
			$role->removeUser($this);
		});
	}

	/******************************** Getters and setters **************************************/

	/**
	 * @param string|null $name
	 */
	public function setName($name)
	{
		$this->name = $name ? $name : null;
	}

	/**
	 * @return string|null
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * @param string|null $notation
	 */
	public function setNotation($notation)
	{
		$this->notation = $notation ? $notation : null;
	}

	/**
	 * @return string|null
	 */
	public function getNotation()
	{
		return $this->notation;
	}

	/**
	 * Returns a list of roles that the user is a member of.
	 *
	 * @return string[]
	 */
	public function getRoles()
	{
		$ret = array();
		foreach ($this->roleEntities as $entity) {
			$ret[] = $entity->getName();
		}

		return $ret;
	}

	public function addLogin(Login $login)
	{
		$this->logins->add($login);
	}

	/**
	 * @return \Venne\Security\Login\Login[]
	 */
	public function getLogins()
	{
		return $this->logins->toArray();
	}

	/**
	 * @param string $service
	 * @return bool
	 */
	public function hasLoginProvider($service)
	{
		foreach ($this->loginProviders as $login) {
			if ($login->getType() === $service) {
				return true;
			}
		}

		return false;
	}

	/**
	 * @param bool $published
	 */
	public function setPublished($published)
	{
		$this->published = (bool) $published;
		$this->invalidateLogins();
	}

	/**
	 * @return bool
	 */
	public function isPublished()
	{
		return $this->published;
	}

	/**
	 * @param string $key
	 */
	public function setKey($key)
	{
		$this->key = $key;
	}

	/**
	 * @return null|string
	 */
	public function getKey()
	{
		return $this->key;
	}

	/**
	 * @param string $email
	 */
	public function setEmail($email)
	{
		if (!\Nette\Utils\Validators::isEmail($email)) {
			throw new \Nette\InvalidArgumentException(sprintf('E-mail must be in correct format. \'%s\' is given.', $email));
		}

		$this->email = $email;
	}

	/**
	 * @return string
	 */
	public function getEmail()
	{
		return $this->email;
	}

	public function addLoginProvider(LoginProvider $loginProvider)
	{
		$this->loginProviders[] = $loginProvider;
		$loginProvider->setUser($this);
	}

	public function setLoginProviders(array $loginProviders)
	{
		$this->loginProviders = new ArrayCollection($loginProviders);
	}

	/**
	 * @return \Venne\Security\LoginProvider[]
	 */
	public function getLoginProviders()
	{
		return $this->loginProviders->toArray();
	}

	/**
	 * @return \DateTime
	 */
	public function getCreated()
	{
		return $this->created;
	}

	/**
	 * @param string $class
	 */
	public function setClass($class)
	{
		$this->class = $class;
	}

	/**
	 * @return string
	 */
	public function getClass()
	{
		return $this->class;
	}

	/**
	 * Generate random key.
	 */
	protected function generateNewKey()
	{
		$this->key = Random::generate(30);
	}

}
