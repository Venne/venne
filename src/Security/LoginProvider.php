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

use Doctrine\ORM\Mapping as ORM;
use Kdyby\Doctrine\Entities\BaseEntity;
use Nette\InvalidArgumentException;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 *
 * @property-read string $uid
 * @property-read string $type
 * @property string|null $websiteUrl
 * @property string|null $profileUrl
 * @property string|null $photoUrl
 * @property string|null $nick
 * @property string|null $name
 * @property string|null $surname
 * @property string|null $description
 * @property string|null $email
 * @property string|null $gender
 * @property int|null $birthDay
 * @property int|null $birthMonth
 * @property int|null $birthYear
 * @property string|null $phone
 * @property string|null $address
 * @property string|null $country
 * @property string|null $region
 * @property string|null $city
 * @property string|null $zip
 *
 * @ORM\Entity
 * @ORM\Table(name="login_provider",
 *     uniqueConstraints={@ORM\UniqueConstraint(name="uniqueId", columns={"uid", "type"})}
 * )
 */
class LoginProvider extends \Kdyby\Doctrine\Entities\BaseEntity
{

	use \Venne\Doctrine\Entities\IdentifiedEntityTrait;

	/** @var string */
	const GENDER_MALE = 'male';

	/** @var string */
	const GENDER_FEMALE = 'female';

	/**
	 * @var \Venne\Security\User
	 *
	 * @ORM\ManyToOne(targetEntity="\Venne\Security\User", inversedBy="loginProviders")
	 * @ORM\JoinColumn(onDelete="CASCADE")
	 */
	protected $user;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="string")
	 */
	protected $uid;

	/**
	 * @var string
	 *
	 * @ORM\Column(type="string")
	 */
	protected $type;

	/**
	 * @var string|null
	 *
	 * @ORM\Column(type="string", nullable=true)
	 */
	protected $websiteUrl;

	/**
	 * @var string|null
	 *
	 * @ORM\Column(type="string", nullable=true)
	 */
	protected $profileUrl;

	/**
	 * @var string|null
	 *
	 * @ORM\Column(type="string", nullable=true)
	 */
	protected $photoUrl;

	/**
	 * @var string|null
	 *
	 * @ORM\Column(type="string", nullable=true)
	 */
	protected $nick;

	/**
	 * @var string|null
	 *
	 * @ORM\Column(type="string", nullable=true)
	 */
	protected $name;

	/**
	 * @var string|null
	 *
	 * @ORM\Column(type="string", nullable=true)
	 */
	protected $surname;

	/**
	 * @var string|null
	 *
	 * @ORM\Column(type="string", nullable=true)
	 */
	protected $description;

	/**
	 * @var string|null
	 *
	 * @ORM\Column(type="string", nullable=true)
	 */
	protected $email;

	/**
	 * @var string|null
	 *
	 * @ORM\Column(type="string", nullable=true)
	 */
	protected $gender;

	/**
	 * @var string|null
	 *
	 * @ORM\Column(type="string", nullable=true)
	 */
	protected $birthDay;

	/**
	 * @var string|null
	 *
	 * @ORM\Column(type="string", nullable=true)
	 */
	protected $birthMonth;

	/**
	 * @var string|null
	 *
	 * @ORM\Column(type="string", nullable=true)
	 */
	protected $birthYear;

	/**
	 * @var string|null
	 *
	 * @ORM\Column(type="string", nullable=true)
	 */
	protected $phone;

	/**
	 * @var string|null
	 *
	 * @ORM\Column(type="string", nullable=true)
	 */
	protected $address;

	/**
	 * @var string|null
	 *
	 * @ORM\Column(type="string", nullable=true)
	 */
	protected $country;

	/**
	 * @var string|null
	 *
	 * @ORM\Column(type="string", nullable=true)
	 */
	protected $region;

	/**
	 * @var string|null
	 *
	 * @ORM\Column(type="string", nullable=true)
	 */
	protected $city;

	/**
	 * @var string|null
	 *
	 * @ORM\Column(type="string", nullable=true)
	 */
	protected $zip;

	/** @var string[] */
	protected static $genders = array(
		self::GENDER_MALE => 'male',
		self::GENDER_FEMALE => 'female',
	);

	/**
	 * @param string $uid
	 * @param string $type
	 */
	public function __construct($uid, $type)
	{
		$this->uid = $uid;
		$this->type = $type;
	}

	/**
	 * @return string
	 */
	public function getUid()
	{
		return $this->uid;
	}

	/**
	 * @param \Venne\Security\User $user
	 */
	public function setUser(User $user)
	{
		$this->user = $user;
	}

	/**
	 * @return string
	 */
	public function getType()
	{
		return $this->type;
	}

	/**
	 * @param $gender
	 * @throws \Nette\InvalidArgumentException
	 */
	public function setGender($gender)
	{
		if ($gender !== null && !isset(self::$genders[$gender])) {
			throw new InvalidArgumentException(sprintf('Gender \'%s\' does not exist.', $gender));
		}

		$this->gender = $gender;
	}

	/**
	 * @return string
	 */
	public function getGender()
	{
		return $this->gender;
	}

}
