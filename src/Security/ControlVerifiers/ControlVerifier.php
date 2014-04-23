<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Security\ControlVerifiers;

use Nette\Application\ForbiddenRequestException;
use Nette\Application\UI\PresenterComponentReflection;
use Nette\InvalidArgumentException;
use Nette\Object;
use Nette\Reflection\Method;
use Nette\Security\User;
use Venne\Security\IControlVerifier;
use Venne\Security\IControlVerifierReader;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class ControlVerifier extends Object implements IControlVerifier
{

	/** @var User */
	protected $user;

	/** @var IControlVerifierReader */
	protected $reader;

	/** @var array */
	protected $_annotationSchema = array();

	/** @var array */
	protected $_presenterAllowed = array();

	/** @var array */
	protected $_methodAllowed = array();


	/**
	 * @param User $user
	 * @param IControlVerifierReader $reader
	 */
	public function __construct(User $user, IControlVerifierReader $reader)
	{
		$this->user = $user;
		$this->reader = $reader;
	}


	/**
	 * @param IControlVerifierReader $reader
	 */
	public function setControlVerifierReader(IControlVerifierReader $reader)
	{
		$this->reader = $reader;
	}


	/**
	 * @return IControlVerifierReader
	 */
	public function getControlVerifierReader()
	{
		return $this->reader;
	}


	/**
	 * @param $element
	 * @return bool
	 * @throws \Nette\InvalidArgumentException
	 */
	public function checkRequirements($element)
	{
		if ($element instanceof Method) {
			return $this->checkMethod($element);
		}

		if ($element instanceof PresenterComponentReflection) {
			return $this->checkPresenter($element);
		}

		throw new InvalidArgumentException("Argument must be instance of 'Nette\Reflection\Method' OR 'Nette\Application\UI\PresenterComponentReflection'");
	}


	/**
	 * @param PresenterComponentReflection $element
	 * @return bool
	 */
	protected function isPresenterAllowedCached(PresenterComponentReflection $element)
	{
		if (!array_key_exists($element->name, $this->_presenterAllowed)) {
			$this->_presenterAllowed[$element->name] = $this->isPresenterAllowed($element);
		}

		return $this->_presenterAllowed[$element->name];
	}


	/**
	 * @param Method $element
	 * @return mixed
	 */
	protected function isMethodAllowedCached(Method $element)
	{
		if (!array_key_exists($element->name, $this->_methodAllowed)) {
			$this->_methodAllowed[$element->name] = $this->isMethodAllowed($element);
		}

		return $this->_methodAllowed[$element->name];
	}


	/**
	 * @param PresenterComponentReflection $element
	 * @return bool
	 */
	protected function checkPresenter(PresenterComponentReflection $element)
	{
		return TRUE;
	}


	/**
	 * @param Method $element
	 * @return bool
	 */
	protected function checkMethod(Method $element)
	{
		$class = $element->class;
		$name = $element->name;
		$schema = $this->reader->getSchema($class);
		$exception = NULL;

		// users
		if (isset($schema[$name]['users']) && count($schema[$name]['users']) > 0) {
			$users = $schema[$name]['users'];

			if (!in_array($this->user->getId(), $users)) {
				$exception = "Access denied for your username: '{$this->user->getId()}'. Require: '" . implode(', ', $users) . "'";
			} else {
				return;
			}
		} // roles
		else if (isset($schema[$name]['roles']) && count($schema[$name]['roles']) > 0) {
			$userRoles = $this->user->getRoles();
			$roles = $schema[$name]['roles'];

			if (count(array_intersect($userRoles, $roles)) == 0) {
				$exception = "Access denied for your roles: '" . implode(', ', $userRoles) . "'. Require one of: '" . implode(', ', $roles) . "'";
			} else {
				return;
			}
		} // resource & privilege
		else if (isset($schema[$name]['resource']) && $schema[$name]['resource']) {
			if (!$this->user->isAllowed($schema[$name]['resource'], $schema[$name]['privilege'])) {
				$exception = "Access denied for resource: {$schema[$name]['resource']}" . ($schema[$name]['privilege'] ? " and privilege: {$schema[$name]['privilege']}" : '');
			} else {
				return;
			}
		}

		if ($exception) {
			throw new ForbiddenRequestException($exception);
		}
	}
}
