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
use Venne\System\Registration\DTO;
use Venne\Utils\Object;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class UserFacade extends Object
{

	/** @var \Kdyby\Doctrine\EntityRepository */
	private $userRepository;

	public function __construct(
		EntityManager $entityManager
	) {
		$this->userRepository = $entityManager->getRepository(User::class);
	}

	/**
	 * @param int $userId
	 * @return \Venne\Security\User\User
	 */
	public function getById($userId)
	{
		$user = $this->userRepository->find($userId);
		if ($user === null) {
			throw new UserNotFoundException($userId);
		}

		return $user;
	}

	/**
	 * @return string[]
	 */
	public function getUserOptions()
	{
		$values = array();

		foreach ($this->userRepository->findAll() as $user) {
			$values[$user->getId()] = $user->getName();
		}

		return $values;
	}

}
