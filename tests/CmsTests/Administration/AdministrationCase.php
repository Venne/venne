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

use Nette\Security\User;
use Venne\Tester\TestCase;

require __DIR__ . '/../bootstrap.php';

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class AdministrationCase extends TestCase
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
	public function setUp()
	{
		parent::setUp();

		$container = $this->helper->getContainer();

		if (!$this->loggedIn) {
			$this->login($container->getByType('Nette\Security\User'), 'admin', 'admin');
			$this->loggedIn = true;
		}
	}


}
