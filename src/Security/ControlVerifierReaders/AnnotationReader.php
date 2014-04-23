<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Security\ControlVerifierReaders;

use Nette\Object;
use Nette\Reflection\ClassType;
use Nette\Reflection\Method;
use Venne\Security\IControlVerifierReader;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class AnnotationReader extends Object implements IControlVerifierReader
{

	/** @var array */
	protected $_annotationSchema = array();


	/**
	 * @param $class
	 */
	public function getSchema($class)
	{
		if (!isset($this->_annotationSchema[$class])) {
			$schema = array();
			$ref = ClassType::from($class);

			if ($ref->hasAnnotation('secured')) {
				foreach ($ref->getMethods() as $method) {
					$name = $method->getName();
					if (substr($name, 0, 6) !== 'action' && substr($name, 0, 6) !== 'handle') {
						continue;
					}

					if ($method->hasAnnotation('secured')) {
						$secured = $method->getAnnotation('secured');
						$name = $method->getName();
						$schema[$name] = array();
						$schema[$name]['resource'] = $this->getSchemaOfResource($method, $secured);
						$schema[$name]['privilege'] = $this->getSchemaOfPrivilege($method, $secured);
						$schema[$name]['roles'] = $this->getSchemaOfRoles($method, $secured);
						$schema[$name]['users'] = $this->getSchemaOfUsers($method, $secured);
					}
				}
			}

			$this->_annotationSchema[$class] = $schema;
		}

		return $this->_annotationSchema[$class];
	}


	/**
	 * @param Method $method
	 * @param $secured
	 * @return null|string
	 */
	protected function getSchemaOfResource(Method $method, $secured)
	{
		$ret = isset($secured['resource']) ? $secured['resource'] : NULL;
		if (!$ret) {
			$s = $method->getDeclaringClass()->getAnnotation('secured');
			$ret = isset($s['resource']) ? $s['resource'] : $method->getDeclaringClass()->getName();
		}
		return $ret;
	}


	/**
	 * @param Method $method
	 * @param $secured
	 * @return null|string
	 */
	protected function getSchemaOfPrivilege(Method $method, $secured)
	{
		$ret = isset($secured['privilege']) ? $secured['privilege'] : NULL;
		if (!$ret) {
			$name = $method->name;
			$prefix = substr($name, 0, 6);
			$ret = ($prefix === 'action' || $prefix === 'handle') ? lcfirst(substr($name, 6)) : $name;
		}
		return $ret;
	}


	/**
	 * @param Method $method
	 * @param $secured
	 * @return array
	 */
	protected function getSchemaOfRoles(Method $method, $secured)
	{
		if (isset($secured['roles'])) {
			$roles = explode(',', $secured['roles']);
			array_walk($roles, function (&$val) {
				$val = trim($val);
			});
			return $roles;
		}
		return array();
	}


	/**
	 * @param Method $method
	 * @param $secured
	 * @return array
	 */
	protected function getSchemaOfUsers(Method $method, $secured)
	{
		if (isset($secured['users'])) {
			$users = explode(',', $secured['users']);
			array_walk($users, function (&$val) {
				$val = trim($val);
			});

			return $users;
		}
		return array();
	}
}
