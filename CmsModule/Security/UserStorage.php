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

use CmsModule\Security\Entities\UserEntity;
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

		$identity = $this->userRepository->findOneBy(array('email' => $identity->id, 'enable' => 1));
		return $identity;
	}


	public function setAuthenticated($state)
	{
		parent::setAuthenticated($state);

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
			return (bool)$this->loginRepository->findOneBy(array('user' => $identity->id, 'sessionId' => $this->session->id));
		} else if ($this->checkConnection->invoke()) {
			try {
				if (!$this->loginRepository->findOneBy(array('user' => NULL, 'sessionId' => $this->session->id))) {
					$this->setAuthenticated(TRUE);
				}
				return TRUE;
			} catch (DBALException $e) {
			}
		}

		return $ret;
	}
}
