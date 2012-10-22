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

use Venne;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 * @Entity(repositoryClass="\CmsModule\Content\Repositories\PageRepository")
 * @Table(name="registrationPage")
 * @DiscriminatorEntry(name="registrationPage")
 */
class RegistrationPageEntity extends PageEntity
{

	const MODE_BASIC = 'basic';

	const MODE_CHECKUP = 'checkup';

	const MODE_MAIL = 'mail';

	const MODE_MAIL_CHECKUP = 'mail & checkup';

	protected static $modes = array(
		self::MODE_BASIC => 'basic registration',
		self::MODE_CHECKUP => 'registration with admin confirmation',
		self::MODE_MAIL => 'registration with e-mail confirmation',
		self::MODE_MAIL_CHECKUP => 'registration with e-mail and admin confirmation'
	);

	/**
	 * @var string
	 * @Column(type="string", nullable=true)
	 */
	protected $userType;

	/**
	 * @var string
	 * @Column(type="string")
	 */
	protected $mode;

	/**
	 * @var \Doctrine\Common\Collections\ArrayCollection|\CmsModule\Security\Entities\RoleEntity
	 * @ManyToMany(targetEntity="\CmsModule\Security\Entities\RoleEntity")
	 * @JoinTable(
	 *      joinColumns={@JoinColumn(onDelete="CASCADE")},
	 *      inverseJoinColumns={@JoinColumn(onDelete="CASCADE")}
	 *      )
	 */
	protected $roles;

	/**
	 * @var string
	 * @Column(type="text")
	 */
	protected $email;

	/**
	 * @var string
	 * @Column(type="string", nullable=true)
	 */
	protected $sender;

	/**
	 * @var string
	 * @Column(type="string", nullable=true)
	 */
	protected $mailFrom;

	/**
	 * @var string
	 * @Column(type="string", nullable=true)
	 */
	protected $subject;


	public function __construct()
	{
		parent::__construct();

		$this->mainRoute->type = 'Cms:Registration:default';
		$this->mode = self::MODE_BASIC;
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
}
