<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\System\UI;

use Nette\Application\ForbiddenRequestException;
use Nette\Application\IPresenterFactory;
use Venne\Security\IControlVerifier;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
trait PresenterTrait
{

	use \Venne\System\UI\ControlTrait;

	/** @var \Venne\Security\IControlVerifier|null */
	private $controlVerifier;

	/** @var \Nette\Application\IPresenterFactory */
	private $presenterFactory;

	/**
	 * @param \Nette\Application\IPresenterFactory $presenterFactory
	 * @param \Venne\Security\IControlVerifier|null $controlVerifier
	 */
	public function injectVennePresenter(
		IPresenterFactory $presenterFactory,
		IControlVerifier $controlVerifier = null
	) {
		$this->presenterFactory = $presenterFactory;
		$this->controlVerifier = $controlVerifier;
	}

	/**
	 * Checks authorization.
	 *
	 * @param mixed $element
	 */
	public function checkRequirements($element)
	{
		if ($this->controlVerifier) {
			$this->controlVerifier->checkRequirements($element);
		}
	}

	/**
	 * @param string $resource
	 * @param string $privilege
	 * @return bool
	 */
	public function isAllowed($resource = null, $privilege = null)
	{
		return $this->getUser()->isAllowed($resource, $privilege);
	}

	/**
	 * @param string $destination in format "[[module:]presenter:]action" or "signal!" or "this"
	 * @param string []
	 * @return bool
	 */
	public function isAuthorized($destination)
	{
		if ($destination == 'this') {
			$class = get_class($this);
			$action = $this->action;
		} elseif (substr($destination, -1, 1) == '!') {
			$class = get_class($this);
			$action = $this->action;
			$do = substr($destination, 0, -1);
		} elseif (ctype_lower(substr($destination, 0, 1))) {
			$class = get_class($this);
			$action = $destination;
		} else {
			if (substr($destination, 0, 1) === ':') {
				$link = substr($destination, 1);
				$link = substr($link, 0, strrpos($link, ':'));
				$action = substr($destination, strrpos($destination, ':') + 1);
			} else {
				$link = substr($this->name, 0, strrpos($this->name, ':'));
				$link = $link . ($link ? ':' : '') . substr($destination, 0, strrpos($destination, ':'));
				$action = substr($destination, strrpos($destination, ':') + 1);
			}
			$action = $action ?: 'default';

			$class = $this->presenterFactory->getPresenterClass($link);
		}

		$schema = $this->controlVerifier->getControlVerifierReader()->getSchema($class);

		if (isset($schema['action' . ucfirst($action)])) {
			$classReflection = new \Nette\Reflection\ClassType($class);
			$method = $classReflection->getMethod('action' . ucfirst($action));

			try {
				$this->controlVerifier->checkRequirements($method);
			} catch (ForbiddenRequestException $e) {
				return false;
			}
		}

		if (isset($do) && isset($schema['handle' . ucfirst($do)])) {
			$classReflection = new \Nette\Reflection\ClassType($class);
			$method = $classReflection->getMethod('handle' . ucfirst($do));

			try {
				$this->controlVerifier->checkRequirements($method);
			} catch (ForbiddenRequestException $e) {
				return false;
			}
		}

		return true;
	}

}
