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

use Doctrine\ORM\EntityManager;
use Nette\Security\AuthenticationException;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class Authenticator extends \Nette\Object implements \Nette\Security\IAuthenticator
{

	/** @var \Doctrine\ORM\EntityManager */
	private $entityManager;

	/** @var \Kdyby\Doctrine\EntityRepository */
	private $userRepository;

	public function __construct(EntityManager $entityManager)
	{
		$this->entityManager = $entityManager;
		$this->userRepository = $entityManager->getRepository(User::class);
	}

	/**
	 * @param string[] $credentials
	 * @return \Nette\Security\Identity
	 */
	public function authenticate(array $credentials)
	{
		list($username, $password) = $credentials;

		/** @var User $user */
		$user = $this->userRepository->findOneBy(array('email' => $username, 'published' => 1));

		if (!$user) {
			throw new AuthenticationException('The username is incorrect.', self::IDENTITY_NOT_FOUND);
		}

		if (!$user->verifyByPassword($password)) {
			throw new AuthenticationException('The password is incorrect.', self::INVALID_CREDENTIAL);
		}

		if ($user->needsRehash()) {
			$user->setPassword($password);
			$this->entityManager->flush($user);
		}

		return new Identity($user->getId(), $user->getRoles(), array(
			'email' => $user->getEmail(),
		));
	}

}
