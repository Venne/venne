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

use CmsModule\Content\Entities\ExtendedRouteEntity;
use CmsModule\Content\Entities\RouteEntity;
use CmsModule\Security\Entities\SocialLoginEntity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Nette\Security\IIdentity;
use Nette\Utils\Strings;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 * @ORM\Entity(repositoryClass="\CmsModule\Security\Repositories\UserRepository")
 * @ORM\Table(name="users")
 */
class UserEntity extends ExtendedRouteEntity implements IIdentity
{

	/**
	 * @ORM\Column(type="string", unique=true, length=64)
	 */
	protected $email = '';

	/**
	 * @ORM\Column(type="string", unique=true, nullable=true)
	 */
	protected $name;

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
	 * @ORM\ManyToMany(targetEntity="\CmsModule\Security\Entities\RoleEntity", cascade={"persist"}, inversedBy="users")
	 * @ORM\JoinTable(name="users_roles",
	 *      joinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE")},
	 *      inverseJoinColumns={@ORM\JoinColumn(name="role_id", referencedColumnName="id", onDelete="CASCADE")}
	 *      )
	 */
	protected $roleEntities;

	/**
	 * @ORM\OneToMany(targetEntity="\CmsModule\Security\Entities\LoginEntity", mappedBy="user")
	 */
	protected $logins;

	/**
	 * @ORM\OneToMany(targetEntity="\CmsModule\Security\Entities\SocialLoginEntity", mappedBy="user", cascade={"persist"})
	 */
	protected $socialLogins;

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
	 * @var RouteEntity[]|ArrayCollection
	 * @ORM\OneToMany(targetEntity="\CmsModule\Content\Entities\RouteEntity", mappedBy="author")
	 */
	protected $routes;

	/**
	 * @ORM\Column(type="string")
	 */
	protected $class;


	protected function startup()
	{
		parent::startup();

		$this->roleEntities = new ArrayCollection;
		$this->logins = new ArrayCollection;
		$this->socialLogins = new ArrayCollection;
		$this->routes = new ArrayCollection;
		$this->generateNewSalt();
		$this->created = new \DateTime;
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
	 *
	 * @param $key
	 */
	public function disableByKey()
	{
		$this->generateNewKey();
	}


	/**
	 * Verify user by key.
	 *
	 * @param $key
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
		return $this->name ? : $this->email;
	}


	/******************************** Getters and setters **************************************/


	public function setName($name)
	{
		$this->name = $name ? $name : NULL;
		$this->generateSlug();
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
		$this->route->notation = $this->notation = $notation;
	}


	/**
	 * @return mixed
	 */
	public function getNotation()
	{
		return $this->notation;
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


	public function getRoleEntities()
	{
		return $this->roleEntities;
	}


	/**
	 * Sets a list of roles that the user is a member of.
	 *
	 * @param  array
	 */
	public function setRoleEntities($roles)
	{
		$this->roleEntities = $roles;
		$this->invalidateLogins();
	}


	public function setLogins($logins)
	{
		$this->logins = $logins;
	}


	public function getLogins()
	{
		return $this->logins;
	}


	public function setPublished($published)
	{
		$this->route->published = $this->published = $published;
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
		$this->generateSlug();
	}


	public function getEmail()
	{
		return $this->email;
	}


	public function addSocialLogin(SocialLoginEntity $socialLogin)
	{
		$this->socialLogins[] = $socialLogin;
		$socialLogin->setUser($this);
	}


	public function setSocialLogins($socialLogins)
	{
		$this->socialLogins = $socialLogins;
	}


	public function getSocialLogins()
	{
		return $this->socialLogins;
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


	/**
	 * @param $routes
	 */
	public function setRoutes($routes)
	{
		$this->routes = $routes;
	}


	/**
	 * @return mixed
	 */
	public function getRoutes()
	{
		return $this->routes;
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


	protected function generateSlug()
	{
		$this->route->setValueForAllTranslations('name', $this->name ? : $this->email);
		$this->route->setValueForAllTranslations('title', $this->name ? : $this->email);
		$this->route->setValueForAllTranslations('localUrl', $this->name ? Strings::webalize($this->name) : $this->email);
	}
}
