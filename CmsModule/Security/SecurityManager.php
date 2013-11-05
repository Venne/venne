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

	/** @var ILoginProvider[] */
	protected $loginProviders = array();


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
	 * @param ILoginProvider $loginProvider
	 * @throws InvalidArgumentException
	 */
	public function addLoginProvider($name, $loginProviderFactoryName)
	{
		if (isset($this->loginProviders[$name])) {
			throw new InvalidArgumentException("Social login name '{$name}' is already installed.");
		}

		$this->loginProviders[$name] = $loginProviderFactoryName;
	}


	/**
	 * @param $name
	 * @return ILoginProvider
	 * @throws InvalidArgumentException
	 */
	public function getLoginProviderByName($name)
	{
		if (!isset($this->loginProviders[$name])) {
			throw new InvalidArgumentException("Social login name '{$name}' has not been registered.");
		}

		return $this->content->getService($this->loginProviders[$name]);
	}


	/**
	 * @return array|ILoginProvider[]
	 */
	public function getLoginProviders()
	{
		return array_keys($this->loginProviders);
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
