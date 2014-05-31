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

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Kdyby\Doctrine\Entities\BaseEntity;
use Nette\Callback;
use Nette\Security\IIdentity;
use Nette\Utils\Strings;
use Venne\Doctrine\Entities\IdentifiedEntityTrait;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 * @ORM\Entity
 * @ORM\EntityListeners({
 *        "\Venne\Security\Listeners\ExtendedUserListener",
 *        "\Venne\Security\Listeners\UserStateListener"
 * })
 * @ORM\Table(name="users")
 */
class UserEntity extends BaseEntity implements IIdentity
{

	use IdentifiedEntityTrait;

	/**
	 * @ORM\Column(type="string", unique=true, length=64)
	 */
	protected $email;

	/**
	 * @ORM\Column(type="string")
	 */
	protected $name = '';

	/**
	 * @ORM\Column(type="text")
	 */
	protected $notation = '';

	/**
	 * @ORM\Column(type="string")
	 */
	protected $password = '';

	/**
	 * @ORM\Column(type="string", name="enableByKey", nullable=true)
	 */
	protected $key;

	/**
	 * @var bool
	 * @ORM\Column(type="boolean")
	 */
	protected $published = TRUE;

	/**
	 * @ORM\Column(type="string")
	 */
	protected $salt;

	/**
	 * @var \Doctrine\Common\Collections\ArrayCollection
	 * @ORM\ManyToMany(targetEntity="RoleEntity", cascade={"persist"}, inversedBy="users")
	 * @ORM\JoinTable(name="users_roles",
	 *      joinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE")},
	 *      inverseJoinColumns={@ORM\JoinColumn(name="role_id", referencedColumnName="id", onDelete="CASCADE")}
	 *      )
	 */
	protected $roleEntities;

	/**
	 * @var LoginEntity[]|ArrayCollection
	 * @ORM\OneToMany(targetEntity="LoginEntity", mappedBy="user")
	 */
	protected $logins;

	/**
	 * @var LoginProviderEntity[]|ArrayCollection
	 * @ORM\OneToMany(targetEntity="LoginProviderEntity", mappedBy="user", cascade={"persist"}, orphanRemoval=true)
	 */
	protected $loginProviders;

	/**
	 * @ORM\Column(type="string", nullable=true)
	 */
	protected $socialType;

	/**
	 * @ORM\Column(type="string", nullable=true)
	 */
	protected $socialData;

	/**
	 * @var \DateTime
	 * @ORM\Column(type="datetime")
	 */
	protected $created;

	/**
	 * @ORM\Column(type="string")
	 */
	protected $class;

	/**
	 * @ORM\Column(type="string", nullable=true)
	 */
	protected $resetKey;

	/**
	 * @var ExtendedUserEntity
	 */
	protected $extendedUser;

	/**
	 * @var callable
	 */
	private $extendedUserCallback;


	/**
	 * @var \Doctrine\Common\Collections\ArrayCollection
	 * @ORM\ManyToMany(targetEntity="UserEntity", cascade={"persist"}, inversedBy="users")
	 * @ORM\JoinTable(name="users_friends",
	 *      joinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE")},
	 *      inverseJoinColumns={@ORM\JoinColumn(name="friend_id", referencedColumnName="id", onDelete="CASCADE")}
	 *      )
	 */
	protected $friends;


	public function __construct()
	{
		$this->roleEntities = new ArrayCollection;
		$this->logins = new ArrayCollection;
		$this->loginProviders = new ArrayCollection;
		$this->friends = new ArrayCollection;
		$this->created = new \DateTime;

		$this->generateNewSalt();
	}


	/**
	 * @param callable $extendedUserCallback
	 */
	public function setExtendedUserCallback($extendedUserCallback)
	{
		$this->extendedUserCallback = $extendedUserCallback;
	}


	/**
	 * @return ExtendedUserEntity
	 */
	public function getExtendedUser()
	{
		if (!$this->extendedUser) {
			$this->extendedUser = Callback::create($this->extendedUserCallback)->invoke();
		}

		return $this->extendedUser;
	}


	/**
	 * Invalidate all user logins.
	 */
	public function invalidateLogins()
	{
		foreach ($this->logins as $login) {
			$login->reload = TRUE;
		}
	}


	/**
	 * Set password.
	 *
	 * @param $password
	 */
	public function setPassword($password)
	{
		if ($password === NULL) {
			return;
		}

		if (strlen($password) < 5) {
			throw new \Nette\InvalidArgumentException('Minimal length of password is 5 chars.');
		}

		$this->password = $this->getHash($password);
	}


	/**
	 * Verify the password.
	 *
	 * @param $password
	 * @return bool
	 */
	public function verifyByPassword($password)
	{
		if (!$this->isEnable()) {
			return FALSE;
		}

		if ($this->password == $this->getHash($password)) {
			return TRUE;
		}

		return FALSE;
	}


	/**
	 * Disable user and verify by key.
	 */
	public function disableByKey()
	{
		$this->generateNewKey();
	}


	/**
	 * Verify user by key.
	 *
	 * @param $key
	 * @return bool
	 */
	public function enableByKey($key)
	{
		if ($this->key == $key) {
			$this->key = NULL;
			return TRUE;
		}
		return FALSE;
	}


	/**
	 * @return string
	 */
	public function resetPassword()
	{
		return $this->resetKey = Strings::random(30);
	}


	public function removeResetKey()
	{
		$this->resetKey = NULL;
	}


	/**
	 * Check if user is enable.
	 *
	 * @return bool
	 */
	public function isEnable()
	{
		if (!$this->key && $this->published) {
			return TRUE;
		}
		return FALSE;
	}


	/**
	 * @return string
	 */
	public function __toString()
	{
		return $this->name ? : (string)$this->email;
	}


	/******************************** Getters and setters **************************************/


	public function setName($name)
	{
		$this->name = $name ? $name : NULL;
	}


	/**
	 * @return mixed
	 */
	public function getName()
	{
		return $this->name;
	}


	/**
	 * @param mixed $notation
	 */
	public function setNotation($notation)
	{
		$this->notation = $notation;
	}


	/**
	 * @return mixed
	 */
	public function getNotation()
	{
		return $this->notation;
	}


	/**
	 * @param RoleEntity $roleEntity
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
	 * @return array
	 */
	public function getRoles()
	{
		$ret = array();
		foreach ($this->roleEntities as $entity) {
			$ret[] = $entity->name;
		}
		return $ret;
	}


	public function setLogins(array $logins)
	{
		$this->logins = new ArrayCollection($logins);
	}


	public function getLogins()
	{
		return $this->logins->toArray();
	}


	/**
	 * @param $service
	 * @return bool
	 */
	public function hasLoginProvider($service)
	{
		foreach ($this->loginProviders as $login) {
			if ($login->getType() === $service) {
				return TRUE;
			}
		}

		return FALSE;
	}


	public function setPublished($published)
	{
		$this->published = $published;
		$this->invalidateLogins();
	}


	/**
	 * @return boolean
	 */
	public function getPublished()
	{
		return $this->published;
	}


	public function setKey($key)
	{
		$this->key = $key;
	}


	public function getKey()
	{
		return $this->key;
	}


	public function setEmail($email)
	{
		if (!\Nette\Utils\Validators::isEmail($email)) {
			throw new \Nette\InvalidArgumentException("E-mail must be in correct format. '{$email}' is given.");
		}

		$this->email = $email;
	}


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
	 * @return LoginProviderEntity[]|ArrayCollection
	 */
	public function getLoginProviders()
	{
		return $this->loginProviders->toArray();
	}


	/**
	 * @param \DateTime $created
	 */
	public function setCreated($created)
	{
		$this->created = $created;
	}


	/**
	 * @return \DateTime
	 */
	public function getCreated()
	{
		return $this->created;
	}


	public function setSocialData($socialData)
	{
		$this->socialData = $socialData;
	}


	public function getSocialData()
	{
		return $this->socialData;
	}


	public function setSocialType($socialType)
	{
		$this->socialType = $socialType;
	}


	public function getSocialType()
	{
		return $this->socialType;
	}


	/**
	 * @param mixed $class
	 */
	public function setClass($class)
	{
		$this->class = $class;
	}


	/**
	 * @return mixed
	 */
	public function getClass()
	{
		return $this->class;
	}


	/******************************** protected function ***************************************/

	/**
	 * Generate random salt.
	 */
	protected function generateNewSalt()
	{
		$this->salt = Strings::random(8);
	}


	/**
	 * Generate random key.
	 */
	protected function generateNewKey()
	{
		$this->key = Strings::random(30);
	}


	/**
	 * Get hash of password.
	 *
	 * @param $password
	 * @return string
	 */
	protected function getHash($password)
	{
		return md5($this->salt . $password);
	}

}
