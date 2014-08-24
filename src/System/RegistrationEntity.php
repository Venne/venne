<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\System;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 *
 * @property bool $enabled
 * @property bool $invitation
 * @property string $userType
 * @property string $mode
 * @property string $loginProviderMode
 * @property \Venne\Security\RoleEntity[] $roles
 *
 * @ORM\Entity
 * @ORM\Table(name="registrations")
 */
class RegistrationEntity extends \Kdyby\Doctrine\Entities\BaseEntity
{

	use \Venne\Doctrine\Entities\NamedEntityTrait;

	const MODE_BASIC = 'basic';

	const MODE_CHECKUP = 'checkup';

	const MODE_MAIL = 'mail';

	const MODE_MAIL_CHECKUP = 'mail&checkup';

	const LOGIN_PROVIDER_MODE_LOAD = 'load';

	const LOGIN_PROVIDER_MODE_LOAD_AND_SAVE = 'load&save';

	/** @var string[] */
	private static $modes = array(
		self::MODE_BASIC => 'basic registration',
		self::MODE_CHECKUP => 'registration with admin confirmation',
		self::MODE_MAIL => 'registration with e-mail confirmation',
		self::MODE_MAIL_CHECKUP => 'registration with e-mail and admin confirmation'
	);

	/** @var string[] */
	private static $loginProviderModes = array(
		self::LOGIN_PROVIDER_MODE_LOAD => 'only load user data',
		self::LOGIN_PROVIDER_MODE_LOAD_AND_SAVE => 'load user data and save',
	);

	/**
	 * @var bool
	 *
	 * @ORM\Column(type="boolean")
	 */
	protected $enabled = false;

	/**
	 * @var bool
	 *
	 * @ORM\Column(type="boolean")
	 */
	protected $invitation = false;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="string")
	 */
	protected $userType;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="string")
	 */
	protected $mode;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="string")
	 */
	protected $loginProviderMode;

	/**
	 * @var \Venne\Security\RoleEntity[]|\Doctrine\Common\Collections\ArrayCollection
	 *
	 * @ORM\ManyToMany(targetEntity="\Venne\Security\RoleEntity")
	 */
	protected $roles;

	public function __construct()
	{
		$this->roles = new ArrayCollection();
	}

	/**
	 * @return string[]
	 */
	public static function getModes()
	{
		return self::$modes;
	}

	/**
	 * @return string[]
	 */
	public static function getLoginProviderModes()
	{
		return self::$loginProviderModes;
	}

}
