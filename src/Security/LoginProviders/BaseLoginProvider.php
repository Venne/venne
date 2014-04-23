<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Security\LoginProviders;

use Venne\System\Pages\Users\UserEntity;
use Venne\Security\Entities\LoginProviderEntity;
use Venne\Security\Identity;
use Venne\Security\ILoginProvider;
use Venne\Security\Repositories\UserRepository;
use DoctrineModule\DI\ConnectionChecker;
use DoctrineModule\DI\ConnectionCheckerFactory;
use Nette\Object;
use Nette\Security\AuthenticationException;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
abstract class BaseLoginProvider extends Object implements ILoginProvider
{

	/** @var array|NULL */
	protected $authenticationParameters;

	/** @var BaseRepository */
	protected $userRepository;

	/** @var ConnectionChecker */
	protected $checkConnection;

	/** @var LoginProviderEntity */
	private $loginProviderEntity;


	/**
	 * @param UserRepository $userRepository
	 * @param ConnectionChecker $checkConnection
	 */
	public function __construct(UserRepository $userRepository, ConnectionChecker $checkConnection)
	{
		$this->userRepository = $userRepository;
		$this->checkConnection = $checkConnection;
	}


	/**
	 * @param UserEntity $userEntity
	 */
	public function connectWithUser(UserEntity $userEntity)
	{
		$userEntity->addLoginProvider($this->getLoginProviderEntity());
		$this->userRepository->save($userEntity);
	}


	/**
	 * @param array $parameters
	 */
	public function setAuthenticationParameters(array $parameters)
	{
		$this->authenticationParameters = $parameters;
	}


	/**
	 * @return \Nette\Forms\Container|NULL
	 */
	public function getFormContainer()
	{
		return NULL;
	}


	/**
	 * @param array $credentials
	 * @return Identity|\Nette\Security\IIdentity
	 * @throws \Nette\Security\AuthenticationException
	 */
	public function authenticate(array $credentials)
	{
		if ($this->checkConnection->invoke()) {
			try {
				/** @var $user \Venne\System\Pages\Users\UserEntity */
				$user = $this->userRepository->createQueryBuilder('a')
					->join('a.loginProviders', 's')
					->where('s.type = :type AND s.uid = :uid')
					->setParameter('type', static::getType())
					->setParameter('uid', $this->getLoginProviderEntity()->getUid())->getQuery()->getSingleResult();
			} catch (\Doctrine\ORM\NoResultException $e) {
			}

			if (!isset($user) || !$user) {
				throw new AuthenticationException('User does not exist.', self::INVALID_CREDENTIAL);
			}

			return new Identity($user->getId(), $user->getRoles());
		}
	}


	/**
	 * @return LoginProviderEntity
	 */
	public function getLoginProviderEntity()
	{
		if (!$this->loginProviderEntity) {
			$this->loginProviderEntity = $this->createLoginProviderEntity();
		}

		return $this->loginProviderEntity;
	}


	abstract protected function createLoginProviderEntity();

}
