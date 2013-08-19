<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace CmsModule\Security;

use DoctrineModule\Entities\IEntity;
use Nette\DI\Container;
use Nette\InvalidArgumentException;
use Nette\Object;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class SecurityManager extends Object
{

	/** @var Container */
	protected $content;

	/** @var UserType[] */
	private $userTypes = array();

	/** @var ISocialLogin[] */
	protected $socialLogins = array();


	/**
	 * @param Container $content
	 */
	public function __construct(Container $content)
	{
		$this->content = $content;
	}


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


	/**
	 * @param $name
	 * @param ISocialLogin $socialLogin
	 * @throws InvalidArgumentException
	 */
	public function addSocialLogin($name, $socialLoginFactoryName)
	{
		if (isset($this->socialLogins[$name])) {
			throw new InvalidArgumentException("Social login name '{$name}' is already installed.");
		}

		$this->socialLogins[$name] = $socialLoginFactoryName;
	}


	/**
	 * @param $name
	 * @return ISocialLogin
	 * @throws InvalidArgumentException
	 */
	public function getSocialLoginByName($name)
	{
		if (!isset($this->socialLogins[$name])) {
			throw new InvalidArgumentException("Social login name '{$name}' has not been registered.");
		}

		return $this->content->getService($this->socialLogins[$name]);
	}


	/**
	 * @return array|ISocialLogin[]
	 */
	public function getSocialLogins()
	{
		return array_keys($this->socialLogins);
	}


	/**************************************** Protected ****************************************************/

	protected function normalizeEntityName($entity)
	{
		if ($entity instanceof IEntity) {
			$entity = get_class($entity);
		}

		return substr($entity, 0, 1) === '/' ? substr($entity, 1) : $entity;
	}
}
