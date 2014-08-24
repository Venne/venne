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

use Doctrine\DBAL\DBALException;
use Kdyby\Doctrine\EntityDao;
use Nette\Http\Session;
use Venne\Security\Repositories\LoginRepository;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class UserStorage extends \Nette\Http\UserStorage
{

	/** @var \Nette\Http\Session */
	private $session;

	/** @var \Kdyby\Doctrine\EntityDao */
	private $loginDao;

	/** @var \Kdyby\Doctrine\EntityDao */
	private $userDao;

	/** @var \Kdyby\Doctrine\EntityDao */
	private $identities = array();

	/** @var array */
	private $logins = array();

	public function  __construct(
		Session $sessionHandler,
		EntityDao $loginDao,
		EntityDao $userDao
	)
	{
		parent::__construct($sessionHandler);

		$this->session = $sessionHandler;
		$this->loginDao = $loginDao;
		$this->userDao = $userDao;
	}

	public function getIdentity()
	{
		$identity = parent::getIdentity();
		if (!$identity instanceof Identity) {
			return $identity;
		}

		if (!isset($this->identities[$identity->id])) {
			$this->identities[$identity->id] = $this->userDao->findOneBy(array('id' => $identity->id, 'published' => 1));
		}

		return $this->identities[$identity->id];
	}

	/**
	 * @param bool $state
	 * @return \Nette\Http\UserStorage|null
	 */
	public function setAuthenticated($state)
	{
		parent::setAuthenticated($state);

		if ($state === false) {
			return;
		}

		if (($identity = $this->getIdentity()) instanceof UserEntity) {
			$loginEntity = new LoginEntity($this->session->id, $identity);
			$this->loginDao->save($loginEntity);
		} else {
			$loginEntity = new LoginEntity($this->session->id, null);
			$this->loginDao->save($loginEntity);
		}
	}

	/**
	 * @return bool
	 */
	public function isAuthenticated()
	{
		if (($ret = parent::isAuthenticated()) === false) {
			return false;
		}

		if (($identity = $this->getIdentity()) === null) {
			return false;
		}

		if ($identity instanceof UserEntity) {
			if (!isset($this->logins[$this->session->id][$identity->id])) {
				$this->logins[$this->session->id][$identity->id] = (bool) $this->loginDao->findOneBy(array('user' => $identity->id, 'sessionId' => $this->session->id));
			}

			return $this->logins[$this->session->id][$identity->id];
		} else {
			try {
				if (!isset($this->logins[$this->session->id][-1])) {
					$this->logins[$this->session->id][-1] = (bool) $this->loginDao->findOneBy(array('user' => null, 'sessionId' => $this->session->id));

					if (!$this->logins[$this->session->id][-1]) {
						$this->setAuthenticated(true);
					}
				}

				return true;
			} catch (DBALException $e) {
			}
		}

		return $ret;
	}

}
