<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Security\Role;

use Doctrine\ORM\EntityManager;
use Venne\Utils\Object;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class RoleFacade extends Object
{

	/** @var \Doctrine\ORM\EntityManager */
	private $entityManager;

	/** @var \Kdyby\Doctrine\EntityRepository */
	private $roleRepository;

	public function __construct(
		EntityManager $entityManager
	) {
		$this->entityManager = $entityManager;
		$this->roleRepository = $entityManager->getRepository(Role::class);
	}

	/**
	 * @param int $roleId
	 * @return \Venne\Security\Role\Role
	 */
	public function getById($roleId)
	{
		$registration = $this->roleRepository->find($roleId);
		if ($registration === null) {
			throw new RoleNotFoundException($roleId);
		}

		return $registration;
	}

	public function saveRole(Role $role)
	{
		$this->entityManager->persist($role);
		$this->entityManager->flush();
	}

	/**
	 * @return string[]
	 */
	public function getRoleOptions()
	{
		$values = array();

		foreach ($this->roleRepository->findAll() as $role) {
			$values[$role->getId()] = $role->getName();
		}

		return $values;
	}

}
