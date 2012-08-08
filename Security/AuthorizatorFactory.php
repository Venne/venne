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

use Venne;
use Nette\Object;
use Nette\Utils\Finder;
use Nette\Reflection\ClassType;
use Nette\Caching\Cache;
use Nette\Caching\Storages\FileStorage;
use Nette\Security\Permission;
use Nette\Security\User;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class AuthorizatorFactory extends Object
{


	const SESSION_SECTION = "Venne.Security.Authorizator";

	/** @var \Nette\DI\Container */
	protected $context;

	/** @var array */
	protected $defaultRoles = array("admin", "authenticated");

	/** @var Cache */
	protected $cache;



	/**
	 * @param \Nette\DI\Container
	 */
	public function __construct(\Nette\DI\Container $context, FileStorage $cacheStorage)
	{
		$this->context = $context;
		$this->cache = new Cache($cacheStorage);
	}



	/**
	 * Get permission for current user.
	 *
	 * @param User $user
	 * @return Permission
	 */
	public function getCurrentPermissions(User $user)
	{
		/** @var $session \Nette\Http\SessionSection */
		$session = $this->context->session->getSection(self::SESSION_SECTION);

		if ($session["permission"]) {
			return $session["permission"];
		}

		$permission = $this->getPermissionsByUser($user);

		return $session["permission"] = $permission;
	}



	/**
	 * Get permission for user.
	 *
	 * @param User $user
	 * @return Permission
	 */
	public function getPermissionsByUser(User $user)
	{
		return $this->getPermissionsByRoles(array_merge($user->roles, array("guest", "authenticated"))); // load with guest
	}



	/**
	 * Get permission for roles.
	 *
	 * @param array $roles
	 * @return Permission
	 */
	public function getPermissionsByRoles(array $roles)
	{
		$permission = $this->getRawPermissions();


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
	protected function setPermissionsByRole(Permission $permission, $role)
	{
		if ($role == "admin") {
			$permission->allow("admin", \Nette\Security\Permission::ALL);
			return $permission;
		}

		$roleEntity = $this->context->cms->roleRepository->findOneByName($role);
		if ($roleEntity) {
			if ($roleEntity->parent) {
				$this->setPermissionsByRole($permission, $roleEntity->parent->name);
			}

			if ($roleEntity && !$permission->hasRole($role)) {
				$permission->addRole($role, $roleEntity->parent ? $roleEntity->parent->name : NULL);
			}

			// allow/deny
			foreach ($roleEntity->permissions as $perm) {
				if ($permission->hasResource($perm->resource)) {
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



	/**
	 * Get raw permissions without privileges.
	 *
	 * @return Permission
	 */
	public function getRawPermissions()
	{
		$permission = new Permission;

		foreach ($this->scanResources() as $resource => $item) {
			$this->addResourceRecursively($permission, $resource);
		}

		foreach ($this->defaultRoles as $role) {
			if (!$permission->hasRole($role)) {
				$permission->addRole($role);
			}
		}

		return $permission;
	}



	/**
	 * Add resource recursively.
	 *
	 * @param string $resource
	 */
	protected function addResourceRecursively($permission, $resource)
	{
		$parent = $this->getNameOfParentResource($resource);
		if ($parent && !$permission->hasResource($parent)) {
			$this->addResourceRecursively($permission, $parent);
		}

		$permission->addResource($resource, $parent);
	}



	/**
	 * Array of all resources.
	 *
	 * @return array
	 */
	protected function scanResources()
	{
		$ret = array();

		foreach ($this->context->findByTag("module") as $key => $module) {
			$module = $this->context->{$key};
			$ret += $this->scanResourcesForPath($module->getPath(), $module->getNamespace());
		}

		return $ret;
	}



	/**
	 * Array of resources with path.
	 *
	 * @param string $path
	 * @param string $namespace
	 */
	protected function scanResourcesForPath($path, $namespace)
	{
		$ret = array();

		foreach (Finder::findFiles("*Presenter.php")->from($path) as $file) {
			$relative = $file->getRealpath();
			$relative = str_replace("/libs-all/venne/App/", "/libs/App/", $relative);
			$relative = strtr($relative, array($path => '', '/' => '\\'));
			$class = $namespace . '\\' . ltrim(substr($relative, 0, -4), '\\');
			$class = str_replace("presenters\\", "", $class);

			$ret += $this->getResourcesInPresenter($class);
		}

		return $ret;
	}



	/**
	 * Array of resources in presenter.
	 *
	 * @param string $class
	 * @return array
	 */
	protected function getResourcesInPresenter($class)
	{
		$ret = array();
		$refl = ClassType::from($class);

		/* class */
		if ($refl->hasAnnotation("secured")) {
			$ret[$class] = true;
		}

		return $ret;
	}



	/**
	 * Name of parent resource.
	 *
	 * @param string $resource
	 * @return string
	 */
	protected function getNameOfParentResource($resource)
	{
		return substr($resource, 0, strrpos($resource, "\\")) ? : NULL;
	}

}
