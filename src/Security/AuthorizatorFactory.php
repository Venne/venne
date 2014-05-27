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

use Kdyby\Doctrine\EntityDao;
use Nette;
use Nette\Application\IPresenterFactory;
use Nette\Http\Session;
use Nette\Http\SessionSection;
use Nette\Object;
use Nette\Security\Permission;
use Venne\System\AdministrationManager;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class AuthorizatorFactory extends Object
{

	const SESSION_SECTION = 'Venne.Security.Authorizator';

	/** @var EntityDao */
	private $roleDao;

	/** @var EntityDao */
	private $permissionDao;

	/** @var SessionSection */
	private $session;

	/** @var AdministrationManager */
	private $administrationManager;


	/**
	 * @param EntityDao $roleDao
	 * @param EntityDao $permissionDao
	 * @param Session $session
	 * @param AdministrationManager $administrationManager
	 */
	public function __construct(
		EntityDao $roleDao,
		EntityDao $permissionDao,
		Session $session,
		AdministrationManager $administrationManager
	)
	{
		$this->roleDao = $roleDao;
		$this->permissionDao = $permissionDao;
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
	 * @param Nette\Security\User $user
	 * @param bool $fromSession
	 * @return Permission
	 */
	public function getPermissionsByUser(Nette\Security\User $user, $fromSession = FALSE)
	{
		if ($fromSession) {
			if ($this->session['permission']) {
				return $this->session['permission'];
			}

			return $this->session['permission'] = $this->getPermissionsByUser($user, FALSE);
		}

		return $this->getPermissionsByRoles($user->roles);
	}


	/**
	 * Get permission for roles.
	 *
	 * @param array $roles
	 * @return Permission
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
	 * @param Permission $permission
	 * @param string $role
	 * @return Permission
	 */
	private function setPermissionsByRole(Permission $permission, $role)
	{
		// add role
		if (!$permission->hasRole($role)) {
			$permission->addRole($role);
		}

		// add resources
		$resources = $this->permissionDao->createQueryBuilder('a')
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
		$roleEntity = $this->roleDao->findOneByName($role);
		if ($roleEntity) {
			if ($roleEntity->parent) {
				$this->setPermissionsByRole($permission, $roleEntity->parent->name);
			}

			if ($roleEntity && !$permission->hasRole($role)) {
				$permission->addRole($role, $roleEntity->parent ? $roleEntity->parent->name : NULL);
			}

			foreach ($roleEntity->permissions as $perm) {
				if ($perm->resource === $permission::ALL || $permission->hasResource($perm->resource)) {
					if ($perm->allow) {
						$permission->allow($role, $perm->resource, $perm->privilege ? $perm->privilege : NULL);
					} else {
						$permission->deny($role, $perm->resource, $perm->privilege ? $perm->privilege : NULL);
					}
				}
			}
		}

		return $permission;
	}

}
