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
use Nette\Security\AuthenticationException;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class Authenticator extends \Nette\Object implements \Nette\Security\IAuthenticator
{

	/** @var \Kdyby\Doctrine\EntityDao */
	private $userDao;

	public function __construct(EntityDao $userDao)
	{
		$this->userDao = $userDao;
	}

	/**
	 * Performs an authentication
	 *
	 * @param string []
	 * @return \Nette\Security\Identity
	 */
	public function authenticate(array $credentials)
	{

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
