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
use Nette\Reflection\Method;
use Nette\Security\User;
use Venne\Security\IControlVerifierReader;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class ControlVerifier extends \Nette\Object implements \Venne\Security\IControlVerifier
{

	/** @var \Nette\Security\User */
	private $user;

	/** @var \Venne\Security\IControlVerifierReader */
	private $reader;

	/** @var mixed[] */
	private $annotationSchema = array();

	/** @var mixed[] */
	private $presenterAllowed = array();

	/** @var mixed[] */
	private $methodAllowed = array();

	public function __construct(User $user, IControlVerifierReader $reader)
	{
		$this->user = $user;
		$this->reader = $reader;
	}

	public function setControlVerifierReader(IControlVerifierReader $reader)
	{
		$this->reader = $reader;
	}

	/**
	 * @return \Venne\Security\IControlVerifierReader
	 */
	public function getControlVerifierReader()
	{
		return $this->reader;
	}

	/**
	 * @param \Nette\Reflection\Method|\Nette\Application\UI\PresenterComponentReflection $element
	 * @return bool
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
	 * @param \Nette\Application\UI\PresenterComponentReflection $element
	 * @return bool
	 */
	protected function isPresenterAllowedCached(PresenterComponentReflection $element)
	{
		if (!array_key_exists($element->name, $this->presenterAllowed)) {
			$this->presenterAllowed[$element->name] = $this->isPresenterAllowed($element);
		}

		return $this->presenterAllowed[$element->name];
	}

	/**
	 * @param \Nette\Reflection\Method $element
	 * @return bool
	 */
	protected function isMethodAllowedCached(Method $element)
	{
		if (!array_key_exists($element->name, $this->methodAllowed)) {
			$this->methodAllowed[$element->name] = $this->isMethodAllowed($element);
		}

		return $this->methodAllowed[$element->name];
	}

	/**
	 * @param \Nette\Application\UI\PresenterComponentReflection $element
	 * @return bool
	 */
	protected function checkPresenter(PresenterComponentReflection $element)
	{
		return true;
	}

	/**
	 * @param \Nette\Reflection\Method $element
	 */
	protected function checkMethod(Method $element)
	{
		$class = $element->class;
		$name = $element->name;
		$schema = $this->reader->getSchema($class);
		$exception = null;

		// users
		if (isset($schema[$name]['users']) && count($schema[$name]['users']) > 0) {
			$users = $schema[$name]['users'];

			if (!in_array($this->user->getId(), $users)) {
				$exception = sprintf(
					'Access denied for your username: \'%s\'. Require: \'%s\'',
					$this->user->getId(),
					implode(', ', $users)
				);
			} else {
				return;
			}
		} elseif (isset($schema[$name]['roles']) && count($schema[$name]['roles']) > 0) {
			$userRoles = $this->user->getRoles();
			$roles = $schema[$name]['roles'];

			if (count(array_intersect($userRoles, $roles)) == 0) {
				$exception = "Access denied for your roles: '" . implode(', ', $userRoles) . "'. Require one of: '" . implode(', ', $roles) . "'";
			} else {
				return;
			}
		} elseif (isset($schema[$name]['resource']) && $schema[$name]['resource']) {
			if (!$this->user->isAllowed($schema[$name]['resource'], $schema[$name]['privilege'])) {
				$exception = sprintf(
					'Access denied for resource: \'%s\' and privilege: \'%s\'',
					$schema[$name]['resource'],
					$schema[$name]['privilege']
				);
			} else {
				return;
			}
		}

		if ($exception) {
			throw new ForbiddenRequestException($exception);
		}
	}

}
