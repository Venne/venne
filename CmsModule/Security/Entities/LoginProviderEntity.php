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

use CmsModule\Pages\Users\UserEntity;
use Doctrine\ORM\Mapping as ORM;
use Nette\InvalidArgumentException;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 *
 * @property-read string $uid
 * @property-read string $type
 * @property string $websiteUrl
 * @property string $profileUrl
 * @property string $photoUrl
 * @property string $nick
 * @property string $name
 * @property string $surname
 * @property string $description
 * @property string $email
 * @property string $gender
 * @property int $birthDay
 * @property int $birthMonth
 * @property int $birthYear
 * @property string $phone
 * @property string $address
 * @property string $country
 * @property string $region
 * @property string $city
 * @property string $zip
 *
 * @ORM\Entity(repositoryClass="\DoctrineModule\Repositories\BaseRepository")
 * @ORM\Table(name="login_provider",
 *     uniqueConstraints={@ORM\UniqueConstraint(name="uniqueId", columns={"uid", "type"})}
 * )
 */
class LoginProviderEntity extends \DoctrineModule\Entities\IdentifiedEntity
{


	/** @var string */
	const GENDER_MALE = 'male';

	/** @var string */
	const GENDER_FEMALE = 'female';

	/**
	 * @var UserEntity
	 * @ORM\ManyToOne(targetEntity="\CmsModule\Pages\Users\UserEntity", inversedBy="loginProviders")
	 * @ORM\JoinColumn(onDelete="CASCADE")
	 */
	protected $user;

	/**
	 * @ORM\Column(type="string")
	 */
	protected $uid;

	/** @ORM\Column(type="string") */
	protected $type;

	/** @ORM\Column(type="string", nullable=true) */
	protected $websiteUrl;

	/** @ORM\Column(type="string", nullable=true) */
	protected $profileUrl;

	/** @ORM\Column(type="string", nullable=true) */
	protected $photoUrl;

	/** @ORM\Column(type="string", nullable=true) */
	protected $nick;

	/** @ORM\Column(type="string", nullable=true) */
	protected $name;

	/** @ORM\Column(type="string", nullable=true) */
	protected $surname;

	/** @ORM\Column(type="text", nullable=true) */
	protected $description;

	/** @ORM\Column(type="string", nullable=true) */
	protected $email;

	/** @ORM\Column(type="string", nullable=true) */
	protected $gender;

	/** @ORM\Column(type="integer", nullable=true) */
	protected $birthDay;

	/** @ORM\Column(type="integer", nullable=true) */
	protected $birthMonth;

	/** @ORM\Column(type="integer", nullable=true) */
	protected $birthYear;

	/** @ORM\Column(type="string", nullable=true) */
	protected $phone;

	/** @ORM\Column(type="string", nullable=true) */
	protected $address;

	/** @ORM\Column(type="string", nullable=true) */
	protected $country;

	/** @ORM\Column(type="string", nullable=true) */
	protected $region;

	/** @ORM\Column(type="string", nullable=true) */
	protected $city;

	/** @ORM\Column(type="string", nullable=true) */
	protected $zip;

	/** @var array */
	protected static $genders = array(
		self::GENDER_MALE => 'male',
		self::GENDER_FEMALE => 'female',
	);


	/**
	 * @param $uid
	 * @param $type
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
	 * @param UserEntity $user
	 */
	public function setUser(UserEntity $user)
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


	public function setUniqueKey($uniqueKey)
	{
		$this->uniqueKey = $uniqueKey;
	}


	public function getUniqueKey()
	{
		return $this->uniqueKey;
	}


	/**
	 * @return int|string
	 */
	public function getId()
	{
		return $this->id;
	}


	/**
	 * @param string $address
	 */
	public function setAddress($address)
	{
		$this->address = $address;
	}


	/**
	 * @return string
	 */
	public function getAddress()
	{
		return $this->address;
	}


	/**
	 * @param int $birthDay
	 */
	public function setBirthDay($birthDay)
	{
		$this->birthDay = $birthDay;
	}


	/**
	 * @return int
	 */
	public function getBirthDay()
	{
		return $this->birthDay;
	}


	/**
	 * @param int $birthMonth
	 */
	public function setBirthMonth($birthMonth)
	{
		$this->birthMonth = $birthMonth;
	}


	/**
	 * @return int
	 */
	public function getBirthMonth()
	{
		return $this->birthMonth;
	}


	/**
	 * @param int $birthYear
	 */
	public function setBirthYear($birthYear)
	{
		$this->birthYear = $birthYear;
	}


	/**
	 * @return int
	 */
	public function getBirthYear()
	{
		return $this->birthYear;
	}


	/**
	 * @param string $city
	 */
	public function setCity($city)
	{
		$this->city = $city;
	}


	/**
	 * @return string
	 */
	public function getCity()
	{
		return $this->city;
	}


	/**
	 * @param string $country
	 */
	public function setCountry($country)
	{
		$this->country = $country;
	}


	/**
	 * @return string
	 */
	public function getCountry()
	{
		return $this->country;
	}


	/**
	 * @param string $description
	 */
	public function setDescription($description)
	{
		$this->description = $description;
	}


	/**
	 * @return string
	 */
	public function getDescription()
	{
		return $this->description;
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
	 * @param $gender
	 * @throws \Nette\InvalidArgumentException
	 */
	public function setGender($gender)
	{
		if ($gender !== NULL && !isset(self::$genders[$gender])) {
			throw new InvalidArgumentException("Gender $gender does not exist.");
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


	/**
	 * @param string $name
	 */
	public function setName($name)
	{
		$this->name = $name;
	}


	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}


	/**
	 * @param string $nick
	 */
	public function setNick($nick)
	{
		$this->nick = $nick;
	}


	/**
	 * @return string
	 */
	public function getNick()
	{
		return $this->nick;
	}


	/**
	 * @param string $phone
	 */
	public function setPhone($phone)
	{
		$this->phone = $phone;
	}


	/**
	 * @return string
	 */
	public function getPhone()
	{
		return $this->phone;
	}


	/**
	 * @param string $photoUrl
	 */
	public function setPhotoUrl($photoUrl)
	{
		$this->photoUrl = $photoUrl;
	}


	/**
	 * @return string
	 */
	public function getPhotoUrl()
	{
		return $this->photoUrl;
	}


	/**
	 * @param string $profileUrl
	 */
	public function setProfileUrl($profileUrl)
	{
		$this->profileUrl = $profileUrl;
	}


	/**
	 * @return string
	 */
	public function getProfileUrl()
	{
		return $this->profileUrl;
	}


	/**
	 * @param string $region
	 */
	public function setRegion($region)
	{
		$this->region = $region;
	}


	/**
	 * @return string
	 */
	public function getRegion()
	{
		return $this->region;
	}


	/**
	 * @param string $surname
	 */
	public function setSurname($surname)
	{
		$this->surname = $surname;
	}


	/**
	 * @return string
	 */
	public function getSurname()
	{
		return $this->surname;
	}


	/**
	 * @param string $websiteUrl
	 */
	public function setWebsiteUrl($websiteUrl)
	{
		$this->websiteUrl = $websiteUrl;
	}


	/**
	 * @return string
	 */
	public function getWebsiteUrl()
	{
		return $this->websiteUrl;
	}


	/**
	 * @param string $zip
	 */
	public function setZip($zip)
	{
		$this->zip = $zip;
	}


	/**
	 * @return string
	 */
	public function getZip()
	{
		return $this->zip;
	}

}
