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

use DoctrineModule\Repositories\BaseRepository;
use Nette\Callback;
use Nette\Security\AuthenticationException;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class Authenticator extends \Venne\Security\Authenticator
{

	/** @var BaseRepository */
	protected $userRepository;

	/** @var Callback */
	protected $checkConnection;


	/**
	 * @param $adminLogin
	 * @param $adminPassword
	 * @param $checkConnection
	 * @param $userRepository
	 */
	public function __construct($adminLogin, $adminPassword, $checkConnection, $userRepository)
	{
		parent::__construct($adminLogin, $adminPassword);

		$this->userRepository = $userRepository;
		$this->checkConnection = $checkConnection;
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
			return parent::authenticate($credentials);
		} catch (AuthenticationException $ex) {
			list($username, $password) = $credentials;

			if ($this->checkConnection->invoke()) {
				$user = $this->userRepository->findOneBy(array('email' => $username, 'enable' => 1));

				if (!$user) {
					throw $ex;
				}

				if (!$user->verifyByPassword($password)) {
					throw new AuthenticationException('The password is incorrect.', self::INVALID_CREDENTIAL);
				}

				return new Identity($username, $user->getRoles());
			}
		}
	}
}
