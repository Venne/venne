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

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Nette\Security\Passwords;
use Nette\Utils\Callback;
use Nette\Utils\Random;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 *
 * @ORM\Entity
 * @ORM\EntityListeners({
 *        "\Venne\Security\Listeners\ExtendedUserListener",
 *        "\Venne\Security\Listeners\UserStateListener"
 * })
 * @ORM\Table(name="users")
 */
class UserEntity extends \Kdyby\Doctrine\Entities\BaseEntity implements \Nette\Security\IIdentity
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
	 * @var \Venne\Security\RoleEntity[]|\Doctrine\Common\Collections\ArrayCollection
	 *
	 * @ORM\ManyToMany(targetEntity="\Venne\Security\RoleEntity", cascade={"persist"}, inversedBy="users")
	 * @ORM\JoinTable(name="users_roles",
	 *      joinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE")},
	 *      inverseJoinColumns={@ORM\JoinColumn(name="role_id", referencedColumnName="id", onDelete="CASCADE")}
	 *      )
	 */
	protected $roleEntities;

	/**
	 * @var \Venne\Security\LoginEntity[]|\Doctrine\Common\Collections\ArrayCollection
	 *
	 * @ORM\OneToMany(targetEntity="\Venne\Security\LoginEntity", mappedBy="user")
	 */
	protected $logins;

	/**
	 * @var \Venne\Security\LoginProviderEntity[]|\Doctrine\Common\Collections\ArrayCollection
	 *
	 * @ORM\OneToMany(targetEntity="\Venne\Security\LoginProviderEntity", mappedBy="user", cascade={"persist"}, orphanRemoval=true)
	 */
	protected $loginProviders;

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
	protected $resetKey;

	/**
	 * @var \Venne\Security\ExtendedUserEntity
	 */
	protected $extendedUser;

	/**
	 * @var callable
	 */
	private $extendedUserCallback;

	/**
	 * @var \Venne\Security\UserEntity[]|\Doctrine\Common\Collections\ArrayCollection
	 *
	 * @ORM\ManyToMany(targetEntity="\Venne\Security\UserEntity", cascade={"persist"}, inversedBy="users")
	 * @ORM\JoinTable(name="users_friends",
	 *      joinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE")},
	 *      inverseJoinColumns={@ORM\JoinColumn(name="friend_id", referencedColumnName="id", onDelete="CASCADE")}
	 *      )
	 */
	protected $friends;

	public function __construct()
	{
		$this->roleEntities = new ArrayCollection();
		$this->logins = new ArrayCollection();
		$this->loginProviders = new ArrayCollection();
		$this->friends = new ArrayCollection();
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
	 * @return \Venne\Security\ExtendedUserEntity
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
	 * @return boolean
	 */
	public function verifyByPassword($password)
	{
		if (!$this->isEnable() || $this->password === null) {
			return false;
		}

		return Passwords::verify($password, $this->password);
	}

	/**
	 * @return boolean
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
	 * @return boolean
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
	 * @return boolean
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
	 * @param \Venne\Security\RoleEntity $roleEntity
	 * @return $this
	 */
	public function addRoleEntity(RoleEntity $roleEntity)
	{
		$this->roleEntities->add($roleEntity);

		return $this;
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
			$ret[] = $entity->name;
		}

		return $ret;
	}

	public function addLogin(LoginEntity $login)
	{
		$this->logins->add($login);
	}

	/**
	 * @return \Venne\Security\LoginEntity[]
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
	public function getPublished()
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

	public function addLoginProvider(LoginProviderEntity $loginProvider)
	{
		$this->loginProviders[] = $loginProvider;
		$loginProvider->setUser($this);
	}

	public function setLoginProviders(array $loginProviders)
	{
		$this->loginProviders = new ArrayCollection($loginProviders);
	}

	/**
	 * @return \Venne\Security\LoginProviderEntity[]
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
	 * @param string $socialData
	 */
	public function setSocialData($socialData)
	{
		$this->socialData = $socialData;
	}

	/**
	 * @return string
	 */
	public function getSocialData()
	{
		return $this->socialData;
	}

	/**
	 * @param string $socialType
	 */
	public function setSocialType($socialType)
	{
		$this->socialType = $socialType;
	}

	/**
	 * @return string
	 */
	public function getSocialType()
	{
		return $this->socialType;
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
