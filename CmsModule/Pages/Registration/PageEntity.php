<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace CmsModule\Pages\Registration;

use CmsModule\Content\Entities\ExtendedPageEntity;
use Doctrine\ORM\Mapping as ORM;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 * @ORM\Entity(repositoryClass="\CmsModule\Content\Repositories\PageRepository")
 * @ORM\Table(name="registration_page")
 */
class PageEntity extends ExtendedPageEntity
{

	const MODE_BASIC = 'basic';

	const MODE_CHECKUP = 'checkup';

	const MODE_MAIL = 'mail';

	const MODE_MAIL_CHECKUP = 'mail&checkup';

	const SOCIAL_MODE_LOAD = 'load';

	const SOCIAL_MODE_LOAD_AND_SAVE = 'load&save';

	protected static $modes = array(
		self::MODE_BASIC => 'basic registration',
		self::MODE_CHECKUP => 'registration with admin confirmation',
		self::MODE_MAIL => 'registration with e-mail confirmation',
		self::MODE_MAIL_CHECKUP => 'registration with e-mail and admin confirmation'
	);

	protected static $socialModes = array(
		self::SOCIAL_MODE_LOAD => 'only load user data',
		self::SOCIAL_MODE_LOAD_AND_SAVE => 'load user data and save',
	);

	/**
	 * @var string
	 * @ORM\Column(type="string")
	 */
	protected $userType = 'CmsModule\Pages\Users\DefaultUserEntity';

	/**
	 * @var string
	 * @ORM\Column(type="string")
	 */
	protected $mode;

	/**
	 * @var string
	 * @ORM\Column(type="string")
	 */
	protected $socialMode;

	/**
	 * @var \Doctrine\Common\Collections\ArrayCollection|\CmsModule\Security\Entities\RoleEntity
	 * @ORM\ManyToMany(targetEntity="\CmsModule\Security\Entities\RoleEntity")
	 * @ORM\JoinTable(
	 *      joinColumns={@ORM\JoinColumn(onDelete="CASCADE")},
	 *      inverseJoinColumns={@ORM\JoinColumn(onDelete="CASCADE")}
	 *      )
	 */
	protected $roles;

	/**
	 * @var string
	 * @ORM\Column(type="text")
	 */
	protected $email;

	/**
	 * @var string
	 * @ORM\Column(type="string", nullable=true)
	 */
	protected $sender;

	/**
	 * @var string
	 * @ORM\Column(type="string", nullable=true)
	 */
	protected $mailFrom;

	/**
	 * @var string
	 * @ORM\Column(type="string", nullable=true)
	 */
	protected $subject;


	protected function startup()
	{
		parent::startup();

		$this->mode = self::MODE_BASIC;
		$this->socialMode = self::SOCIAL_MODE_LOAD_AND_SAVE;
		$this->email = '<p>Thank your for your registration.</p>
			<p>Your registration informations:</p>

			<strong>E-mail:</strong> {$email}<br />
			<strong>Password:</strong> {$password}

			<p>
				Please activate your account here: {$link}
			</p>';
	}


	/**
	 * @param string $userType
	 */
	public function setUserType($userType)
	{
		$this->userType = $userType;
	}


	/**
	 * @return string
	 */
	public function getUserType()
	{
		return $this->userType;
	}


	/**
	 * @param \CmsModule\Security\Entities\RoleEntity|\Doctrine\Common\Collections\ArrayCollection $roles
	 */
	public function setRoles($roles)
	{
		$this->roles = $roles;
	}


	/**
	 * @return \CmsModule\Security\Entities\RoleEntity|\Doctrine\Common\Collections\ArrayCollection
	 */
	public function getRoles()
	{
		return $this->roles;
	}


	/**
	 * @param string $mode
	 */
	public function setMode($mode)
	{
		$this->mode = $mode;
	}


	/**
	 * @return string
	 */
	public function getMode()
	{
		return $this->mode;
	}


	/**
	 * @param string $socialMode
	 */
	public function setSocialMode($socialMode)
	{
		$this->socialMode = $socialMode;
	}


	/**
	 * @return string
	 */
	public function getSocialMode()
	{
		return $this->socialMode;
	}


	/**
	 * @param string $email
	 */
	public function setEmail($email)
	{
		$this->email = $email;
	}


	/**
	 * @return string
	 */
	public function getEmail()
	{
		return $this->email;
	}


	/**
	 * @param string $sender
	 */
	public function setSender($sender)
	{
		$this->sender = $sender;
	}


	/**
	 * @return string
	 */
	public function getSender()
	{
		return $this->sender;
	}


	/**
	 * @param string $subject
	 */
	public function setSubject($subject)
	{
		$this->subject = $subject;
	}


	/**
	 * @return string
	 */
	public function getSubject()
	{
		return $this->subject;
	}


	/**
	 * @param string $mailFrom
	 */
	public function setMailFrom($mailFrom)
	{
		$this->mailFrom = $mailFrom;
	}


	/**
	 * @return string
	 */
	public function getMailFrom()
	{
		return $this->mailFrom;
	}


	public static function getModes()
	{
		return self::$modes;
	}


	public static function getSocialModes()
	{
		return self::$socialModes;
	}
}
