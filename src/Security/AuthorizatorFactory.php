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
use Nette;
use Nette\Http\Session;
use Venne\System\AdministrationManager;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class AuthorizatorFactory extends \Nette\Object
{

	const SESSION_SECTION = 'Venne.Security.Authorizator';

	/** @var \Kdyby\Doctrine\EntityRepository */
	private $roleRepository;

	/** @var \Kdyby\Doctrine\EntityRepository */
	private $permissionRepository;

	/** @var \Nette\Http\SessionSection */
	private $session;

	/** @var \Venne\System\AdministrationManager */
	private $administrationManager;

	public function __construct(
		EntityManager $entityManager,
		Session $session,
		AdministrationManager $administrationManager
	)
	{
		$this->roleRepository = $entityManager->getRepository(Role::class);
		$this->permissionRepository = $entityManager->getRepository(Permission::class);
		$this->session = $session->getSection(self::SESSION_SECTION);
		$this->administrationManager = $administrationManager;
	}

	public function clearPermissionSession()
	{
		if (isset($this->session['permission'])) {
			unset($this->session['permission']);
		}
	}

	/**
	 * @param \Nette\Security\User $user
	 * @param bool $fromSession
	 * @return \Nette\Security\Permission
	 */
	public function getPermissionsByUser(Nette\Security\User $user, $fromSession = false)
	{
		if ($fromSession) {
			if ($this->session['permission']) {
				return $this->session['permission'];
			}

			return $this->session['permission'] = $this->getPermissionsByUser($user, false);
		}

		return $this->getPermissionsByRoles($user->roles);
	}

	/**
	 * Get permission for roles.
	 *
	 * @param string $roles
	 * @return \Venne\Security\Authorizator
	 */
	public function getPermissionsByRoles(array $roles)
	{
		$permission = new Authorizator;

		foreach ($roles as $role) {
			$this->setPermissionsByRole($permission, $role);
		}

		return $permission;
	}

	/**
	 * Setup permission by role
	 *
	 * @param \Venne\Security\Authorizator $permission
	 * @param string $role
	 * @return \Venne\Security\Authorizator
	 */
	private function setPermissionsByRole(Authorizator $permission, $role)
	{
		// add role
		if (!$permission->hasRole($role)) {
			$permission->addRole($role);
		}

		// add resources
		$resources = $this->permissionRepository->createQueryBuilder('a')
			->select('a.resource')
			->andWhere('a.role = :role')->setParameter('role', $role)
			->groupBy('a.resource')
			->getQuery()
			->getResult();

		foreach ($resources as $resource) {
			if (!$permission->hasResource($resource)) {
				$permission->addResource($resource);
			}
		}

		// set allow/deny
		$roleEntity = $this->roleRepository->findOneByName($role);
		if ($roleEntity) {
			if ($roleEntity->parent) {
				$this->setPermissionsByRole($permission, $roleEntity->parent->name);
			}

			if ($roleEntity && !$permission->hasRole($role)) {
				$permission->addRole($role, $roleEntity->parent ? $roleEntity->parent->name : null);
			}

			foreach ($roleEntity->permissions as $perm) {
				if ($perm->resource === $permission::ALL || $permission->hasResource($perm->resource)) {
					if ($perm->allow) {
						$permission->allow($role, $perm->resource, $perm->privilege ? $perm->privilege : null);
					} else {
						$permission->deny($role, $perm->resource, $perm->privilege ? $perm->privilege : null);
					}
				}
			}
		}

		return $permission;
	}

}
