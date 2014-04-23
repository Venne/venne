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

use Nette\Application\IPresenterFactory;
use Nette\Application\Responses\TextResponse;
use Nette\DI\Container;
use Nette\InvalidArgumentException;
use Nette\InvalidStateException;
use Nette\Mail\IMailer;
use Nette\Mail\Message;
use Nette\Object;
use Nette\Utils\Strings;
use Venne\System\AdminModule\EmailPresenter;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class SecurityManager extends Object
{

	/** @var Container */
	private $context;

	/** @var UserType[] */
	private $userTypes = array();

	/** @var ILoginProvider[] */
	private $loginProviders = array();


	/**
	 * @param Container $context
	 */
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
			throw new InvalidArgumentException("Type '$class' does not exist.");
		}

		return $this->userTypes[$class];
	}


	/**
	 * @param $name
	 * @param $loginProviderFactoryName
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

		return $this->context->getService($this->loginProviders[$name]);
	}


	/**
	 * @return array|ILoginProvider[]
	 */
	public function getLoginProviders()
	{
		return array_keys($this->loginProviders);
	}

}
