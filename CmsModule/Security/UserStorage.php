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

use CmsModule\Pages\Users\UserEntity;
use CmsModule\Security\Repositories\LoginRepository;
use CmsModule\Security\Repositories\UserRepository;
use Doctrine\DBAL\DBALException;
use Nette\Callback;
use Nette\Http\Session;
use Nette\InvalidArgumentException;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class UserStorage extends \Nette\Http\UserStorage
{

	/** @var Session */
	private $session;

	/** @var Callback */
	private $checkConnection;

	/** @var LoginRepository */
	private $loginRepository;

	/** @var UserRepository */
	private $userRepository;

	/** @var UserEntity */
	private $identities = array();

	/** @var array */
	private $logins = array();


	public function  __construct(Session $sessionHandler, LoginRepository $loginRepository, UserRepository $userRepository, $checkConnection)
	{
		parent::__construct($sessionHandler);

		$this->session = $sessionHandler;
		$this->loginRepository = $loginRepository;
		$this->userRepository = $userRepository;
		$this->checkConnection = $checkConnection;
	}


	public function getIdentity()
	{
		$identity = parent::getIdentity();
		if (!$identity instanceof Identity) {
			return $identity;
		}

		if (!$this->checkConnection->invoke()) {
			throw new InvalidArgumentException('Database connection not found');
		}

		if (!isset($this->identities[$identity->id])) {
			$this->identities[$identity->id] = $this->userRepository->findOneBy(array('email' => $identity->id, 'published' => 1));
		}

		return $this->identities[$identity->id];
	}


	public function setAuthenticated($state)
	{
		parent::setAuthenticated($state);

		if ($state === FALSE) {
			return;
		}

		if (($identity = $this->getIdentity()) instanceof UserEntity) {
			$loginEntity = $this->loginRepository->createNew(array($this->session->id, $identity));
			$this->loginRepository->save($loginEntity);
		} else if ($this->checkConnection->invoke()) {
			$loginEntity = $this->loginRepository->createNew(array($this->session->id, NULL));
			$this->loginRepository->save($loginEntity);
		}
	}


	public function isAuthenticated()
	{
		if (($ret = parent::isAuthenticated()) === FALSE) {
			return FALSE;
		}

		if (($identity = $this->getIdentity()) === NULL) {
			return FALSE;
		}

		if ($identity instanceof UserEntity) {
			if (!isset($this->logins[$this->session->id][$identity->id])) {
				$this->logins[$this->session->id][$identity->id] = (bool)$this->loginRepository->findOneBy(array('user' => $identity->id, 'sessionId' => $this->session->id));
			}

			return $this->logins[$this->session->id][$identity->id];
		} else if ($this->checkConnection->invoke()) {
			try {
				if (!isset($this->logins[$this->session->id][-1])) {
					$this->logins[$this->session->id][-1] = (bool)$this->loginRepository->findOneBy(array('user' => NULL, 'sessionId' => $this->session->id));

					if (!$this->logins[$this->session->id][-1]) {
						$this->setAuthenticated(TRUE);
					}
				}

				return TRUE;
			} catch (DBALException $e) {
			}
		}

		return $ret;
	}
}
