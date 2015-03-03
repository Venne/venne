<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Security\User;

use Doctrine\ORM\EntityManager;
use Nette;
use Nette\Http\Session;
use Nette\Security\Identity;
use Nette\Security\IIdentity;
use Venne\Security\Login\Login;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class UserStorage extends \Nette\Http\UserStorage
{

	/** @var \Doctrine\ORM\EntityManager */
	private $entityManager;

	/** @var \Kdyby\Doctrine\EntityRepository */
	private $loginRepository;

	/** @var \Venne\Security\User\UserFacade */
	private $userFacade;

	/** @var \Nette\Http\Session */
	private $sessionHandler;

	/** @var \Venne\Security\User\User|null */
	private $user;

	public function  __construct(
		Session $sessionHandler,
		EntityManager $entityManager,
		UserFacade $userFacade
	)
	{
  		parent::__construct($sessionHandler);
		$this->entityManager = $entityManager;
		$this->loginRepository = $entityManager->getRepository(Login::class);
		$this->sessionHandler = $sessionHandler;
		$this->userFacade = $userFacade;
	}

	/**
	 * @param bool $state
	 * @return static
	 */
	public function setAuthenticated($state)
	{
		parent::setAuthenticated($state);

		if ($state && $this->user !== null) {
			$login = new Login($this->sessionHandler->getId(), $this->user);
			$this->entityManager->persist($login);
			$this->entityManager->flush($login);
		}

		return $this;
	}

	/**
	 * @return bool
	 */
	public function isAuthenticated()
	{
		if ($this->user === null) {
			$this->getIdentity();
		}

		if ($this->user === null || !parent::isAuthenticated()) {
			return false;
		}

		$login = $this->loginRepository->find($this->sessionHandler->getId());

		return $login !== null && $login->getUser() === $this->user;
	}

	/**
	 * @param \Nette\Security\IIdentity $identity
	 * @return static
	 */
	public function setIdentity(IIdentity $identity = null)
	{
		if ($identity instanceof User) {
			$this->user = $identity;

			return parent::setIdentity(new Identity($identity->getId()));
		}

		return parent::setIdentity($identity);
	}

	/**
	 * @return \Venne\Security\User\User
	 */
	public function getIdentity()
	{
		$identity = parent::getIdentity();

		$this->user = $identity !== null
			? $this->userFacade->getById(parent::getIdentity()->getId())
			: null;

		return $this->user;
	}


}
