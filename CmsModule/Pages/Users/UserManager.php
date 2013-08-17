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

use Nette\InvalidArgumentException;
use Nette\Object;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 *
 * @property-read UserType[] $userTypes
 */
class UserManager extends Object
{

	/** @var UserType[] */
	private $userTypes = array();


	public function addUserType(UserType $userType)
	{
		$type = $userType->getEntityName();
		if (isset($this->userTypes[$type])) {
			throw new InvalidArgumentException("Type '$type' is already exists.");
		}

		$this->userTypes[$type] = $userType;
	}


	/**
	 * @return UserType[]
	 */
	public function getUserTypes()
	{
		return $this->userTypes;
	}


	/**
	 * @param $class
	 * @return UserType
	 * @throws InvalidArgumentException
	 */
	public function getUserTypeByClass($class)
	{
		if (!isset($this->userTypes[$class])) {
			throw new InvalidArgumentException("Type '$type' does not exist.");
		}

		return $this->userTypes[$class];
	}
}
