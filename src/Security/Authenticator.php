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

use Kdyby\Doctrine\EntityDao;
use Nette\Object;
use Nette\Security\AuthenticationException;
use Nette\Security\IAuthenticator;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class Authenticator extends Object implements IAuthenticator
{

	/** @var EntityDao */
	private $userDao;

	/** @var string */
	private $adminLogin;

	/** @var string */
	private $adminPassword;


	/**
	 * @param $adminLogin
	 * @param $adminPassword
	 * @param $userDao
	 */
	public function __construct($adminLogin, $adminPassword, EntityDao $userDao)
	{
		$this->adminLogin = $adminLogin;
		$this->adminPassword = $adminPassword;
		$this->userDao = $userDao;
	}


	/**
	 * Performs an authentication
	 *
	 * @param  array
	 * @return \Nette\Security\Identity
	 * @throws AuthenticationException
	 */
	public function authenticate(array $credentials)
	{
		try {
			return $this->authenticateAdmin($credentials);
		} catch (\Exception $ex) {
			list($username, $password) = $credentials;

			$user = $this->userDao->findOneBy(array('email' => $username, 'published' => 1));

			if (!$user) {
				throw $ex;
			}

			if (!$user->verifyByPassword($password)) {
				throw new AuthenticationException('The password is incorrect.', self::INVALID_CREDENTIAL);
			}

			return new Identity($user->id, $user->getRoles());
		}
	}


	/**
	 * Performs an authentication
	 *
	 * @param  array
	 * @return \Nette\Security\Identity
	 * @throws \Nette\Security\AuthenticationException
	 */
	private function authenticateAdmin(array $credentials)
	{
		list($username, $password) = $credentials;

		if (!$username OR !$password) {
			throw new AuthenticationException('The username or password is not filled.', self::INVALID_CREDENTIAL);
		}

		if ($this->adminLogin != $username) {
			throw new AuthenticationException('The username is incorrect.', self::INVALID_CREDENTIAL);
		}

		if ($this->adminPassword != $password) {
			throw new AuthenticationException('The password is incorrect.', self::IDENTITY_NOT_FOUND);
		}

		return new \Nette\Security\Identity($username, array('admin'));
	}
}
