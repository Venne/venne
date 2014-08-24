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

use Nette\DI\Container;
use Nette\InvalidArgumentException;
use Venne\System\AdminModule\EmailPresenter;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class SecurityManager extends \Nette\Object
{

	/** @var \Nette\DI\Container */
	private $context;

	/** @var \Venne\Security\UserType[] */
	private $userTypes = array();

	/** @var \Venne\Security\ILoginProvider[] */
	private $loginProviders = array();

	public function __construct(Container $context)
	{
		$this->context = $context;
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
	 * @return \Venne\Security\UserType[]
	 */
	public function getUserTypes()
	{
		return $this->userTypes;
	}

	/**
	 * @param string $class
	 * @return \Venne\Security\UserType
	 */
	public function getUserTypeByClass($class)
	{
		if (!isset($this->userTypes[$class])) {
			throw new InvalidArgumentException(sprintf('Type \'%s\' does not exist.', $class));
		}

		return $this->userTypes[$class];
	}

	/**
	 * @param string $name
	 * @param string $loginProviderFactoryName
	 */
	public function addLoginProvider($name, $loginProviderFactoryName)
	{
		if (isset($this->loginProviders[$name])) {
			throw new InvalidArgumentException(sprintf('Social login name \'%s\' is already installed.', $name));
		}

		$this->loginProviders[$name] = $loginProviderFactoryName;
	}

	/**
	 * @param string $name
	 * @return \Venne\Security\ILoginProvider
	 */
	public function getLoginProviderByName($name)
	{
		if (!isset($this->loginProviders[$name])) {
			throw new InvalidArgumentException("Social login name '{$name}' has not been registered.");
		}

		return $this->context->getService($this->loginProviders[$name]);
	}

	/**
	 * @return \Venne\Security\ILoginProvider[]
	 */
	public function getLoginProviders()
	{
		return array_keys($this->loginProviders);
	}

}
