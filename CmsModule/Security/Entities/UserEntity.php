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
use DoctrineModule\Entities\IdentifiedEntity;
use Nette\Security\IIdentity;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 * @ORM\Entity(repositoryClass="\CmsModule\Security\Repositories\UserRepository")
 * @ORM\Table(name="user")
 * @ORM\InheritanceType("JOINED")
 * @ORM\DiscriminatorColumn(name="type", type="string")
 * @ORM\DiscriminatorMap({"base" = "UserEntity"})
 */
class UserEntity extends IdentifiedEntity implements \DoctrineModule\Entities\IEntity, IIdentity
{

	/**
	 * @ORM\Column(type="boolean")
	 */
	protected $enable = false;

	/**
	 * @ORM\Column(type="string", unique=true, length=64)
	 */
	protected $email = '';

	/**
	 * @ORM\Column(type="string")
	 */
	protected $password = '';

	/**
	 * @ORM\Column(type="string", name="`key`", nullable=true)
	 */
	protected $key;

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


	public function __construct()
	{
		$this->roleEntities = new \Doctrine\Common\Collections\ArrayCollection();
		$this->logins = new \Doctrine\Common\Collections\ArrayCollection();
		$this->socialLogins = new \Doctrine\Common\Collections\ArrayCollection();
		$this->generateNewSalt();
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
			return false;
		}

		if ($this->password == $this->getHash($password)) {
			return true;
		}

		return false;
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
		if (!$this->key && $this->enable) {
			return true;
		}
		return false;
	}


	/**
	 * @return string
	 */
	public function __toString()
	{
		return $this->email;
	}


	/******************************** Getters and setters **************************************/


	public function setId($id)
	{
		$this->id = $id;
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


	public function setEnable($enable)
	{
		$this->enable = $enable;
		$this->invalidateLogins();
	}


	public function getEnable()
	{
		return $this->enable;
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


	/******************************** protected function ***************************************/

	/**
	 * Generate random salt.
	 */
	protected function generateNewSalt()
	{
		$this->salt = \Nette\Utils\Strings::random(8);
	}


	/**
	 * Generate random key.
	 */
	protected function generateNewKey()
	{
		$this->key = \Nette\Utils\Strings::random(30);
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
}
