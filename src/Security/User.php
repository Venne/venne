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

use Nette\Application\UI\Control;
use Nette\DI\Container;
use Nette\Reflection\ClassType;
use Nette\Security\IAuthorizator;
use Nette\Security\IUserStorage;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class User extends \Nette\Security\User
{

	/** @var string[] */
	protected $_presenterAllowed = array();

	/** @var string[] */
	protected $_methodAllowed = array();

	/** @var \Nette\DI\Container */
	private $context;

	public function __construct(IUserStorage $storage, Container $context)
	{
		parent::__construct($storage, null);

		$this->context = $context;
	}

	/**
	 * @return \Nette\Security\IAuthenticator
	 */
	public function getAuthenticator($need = true)
	{
		return $this->context->getByType('Nette\Security\IAuthenticator');
	}

	/**
	 * @return \Nette\Security\IAuthorizator
	 */
	public function getAuthorizator($need = true)
	{
		return $this->context->getByType('Nette\Security\IAuthorizator');
	}

	/**
	 * Has a user effective access to the Resource?
	 * If $resource is NULL, then the query applies to all resources.
	 *
	 * @param string $resource
	 * @param string $privilege
	 * @return bool
	 */
	public function isAllowed($resource = IAuthorizator::ALL, $privilege = IAuthorizator::ALL)
	{
		if ($resource instanceof \Nette\Reflection\Method) {
			return $this->isMethodAllowedCached($resource);
		}

		if ($resource instanceof \Nette\Application\UI\PresenterComponentReflection) {
			return $this->isPresenterAllowedCached($resource);
		}

		if ($resource instanceof Control) {
			return $this->isControlAllowed($resource);
		}

		return parent::isAllowed($resource, $privilege);
	}

	/**
	 * @param \Nette\Application\UI\PresenterComponentReflection $element
	 * @return bool
	 */
	protected function isPresenterAllowedCached(\Nette\Application\UI\PresenterComponentReflection $element)
	{
		if (!array_key_exists($element->name, $this->_presenterAllowed)) {
			$this->_presenterAllowed[$element->name] = $this->isPresenterAllowed($element);
		}

		return $this->_presenterAllowed[$element->name];
	}

	/**
	 * @param \Nette\Reflection\Method $element
	 * @return bool
	 */
	protected function isMethodAllowedCached(\Nette\Reflection\Method $element)
	{
		if (!array_key_exists($element->name, $this->_methodAllowed)) {
			$this->_methodAllowed[$element->name] = $this->isMethodAllowed($element);
		}

		return $this->_methodAllowed[$element->name];
	}

	/**
	 * @param \Nette\Application\UI\PresenterComponentReflection $element
	 * @return bool
	 */
	protected function isPresenterAllowed(\Nette\Application\UI\PresenterComponentReflection $element)
	{
		$ref = ClassType::from($element->name);

		// is not secured
		if (!$ref->hasAnnotation('secured')) {
			return true;
		}

		// resource & privilege
		$secured = $ref->getAnnotation('secured');
		$resource = isset($secured['resource']) ? $secured['resource'] : $ref->getNamespaceName();
		$privilege = isset($secured['privilege']) ? $secured['privilege'] : null;
		if (!parent::isAllowed($resource, $privilege)) {
			return false;
		}

		// roles
		if (isset($secured['roles'])) {
			$userRoles = $this->getRoles();
			$roles = explode(',', $secured['roles']);
			array_walk($roles, function (&$val) {
				$val = trim($val);
			});

			if (count(array_intersect($userRoles, $roles)) == 0) {
				return false;
			}
		}

		// users
		if (isset($secured['users'])) {
			$users = explode(',', $secured['users']);
			array_walk($users, function (&$val) {
				$val = trim($val);
			});

			$users = (array) $element->getAnnotation('User');
			if (in_array($this->getId(), $users)) {
				return false;
			}
		}

		return true;
	}

	/**
	 * @param \Nette\Reflection\Method $element
	 * @return bool
	 */
	protected function isMethodAllowed(\Nette\Reflection\Method $element)
	{
		$classRef = new \Nette\Application\UI\PresenterComponentReflection($element->class);
		$ref = ClassType::from($element->class);

		if (!$this->isPresenterAllowedCached($classRef)) {
			return false;
		}

		$ref = $ref->getMethod($element->name);

		// is not secured
		if (!$ref->hasAnnotation('secured')) {
			return true;
		}

		// resource & privilege
		$secured = $ref->getAnnotation('secured');
		$resource = isset($secured['resource']) ? $secured['resource'] : null;
		if (!$resource) {
			$s = $classRef->getAnnotation('secured');
			$resource = isset($s['resource']) ? $s['resource'] : $classRef->getNamespaceName();
		}
		$privilege = isset($secured['privilege']) ? $secured['privilege'] : $element->name;
		if (!parent::isAllowed($resource, $privilege)) {
			return false;
		}

		// roles
		if (isset($secured['roles'])) {
			$userRoles = $this->getRoles();
			$roles = explode(',', $secured['roles']);
			array_walk($roles, function (&$val) {
				$val = trim($val);
			});

			if (count(array_intersect($userRoles, $roles)) == 0) {
				return false;
			}
		}

		// users
		if (isset($secured['users'])) {
			$users = explode(',', $secured['users']);
			array_walk($users, function (&$val) {
				$val = trim($val);
			});

			$users = (array) $element->getAnnotation('User');
			if (in_array($this->getId(), $users)) {
				return false;
			}
		}

		return true;
	}

	/**
	 * @param \Nette\Application\UI\Control $control
	 * @return bool
	 */
	protected function isControlAllowed(Control $control)
	{
		return true;
	}

}
