<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace CmsTests\Administration;

use CmsTests\PresenterCase;
use Nette\Security\User;

require __DIR__ . '/../PresenterCase.php';

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class AdministrationCase extends PresenterCase
{

	/** @var bool */
	private $loggedIn = false;


	/**
	 * @param User $user
	 * @param $name
	 * @param $password
	 */
	private function login(User $user, $name, $password)
	{
		$user->login($name, $password);
	}


	/**
	 * @return \Nette\DI\Container|\SystemContainer
	 */
	protected function getContainer()
	{
		$container = parent::getContainer();

		if (!$this->loggedIn) {
			$this->login($container->getByType('Nette\Security\User'), 'admin', 'admin');
			$this->loggedIn = true;
		}

		return $container;
	}


}
