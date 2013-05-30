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

use Tester\DomQuery;
use Venne\Tester\TestCase;

require __DIR__ . '/AdministrationCase.php';

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class LoginPresenterTest extends TestCase
{

	public function testPresenter()
	{
		$this->helper->createResponse('Cms:Admin:Login', 'GET', array())
			->type('Nette\Application\Responses\TextResponse')
			->getTemplate()->type('Nette\Templating\ITemplate')
			->getDom()
			->has('input[name="username"]')
			->has('input[name="password"]')
			->has('input[name="remember"]')
			->has('input[name="_submit"]');
	}


	public function testLogin()
	{
		$this->helper->createResponse('Cms:Admin:Login', 'POST', array('do' => 'signInForm-submit'), array(
			'username' => 'admin', 'password' => 'admin', 'remember' => FALSE, '_submit' => 'Přihlásit se'
		))
			->type('Nette\Application\Responses\RedirectResponse');
	}


	public function testBadPassword()
	{
		$this->helper->createResponse('Cms:Admin:Login', 'POST', array('do' => 'signInForm-submit'), array(
			'username' => 'admin', 'password' => 'wrong', 'remember' => FALSE, '_submit' => 'Přihlásit se'
		))
			->type('Nette\Application\Responses\TextResponse')
			->getTemplate()->type('Nette\Templating\ITemplate')
			->getDom()
			->has('div[class="alert alert-warning"]')
			->hasCount(1, 'div[class="alert alert-warning"]')
			->contains('The password is incorrect.', 'div[class="alert alert-warning"]');
	}


	public function testBadUsername()
	{
		$this->helper->createResponse('Cms:Admin:Login', 'POST', array('do' => 'signInForm-submit'), array(
			'username' => 'wrong', 'password' => 'wrong', 'remember' => FALSE, '_submit' => 'Přihlásit se'
		))
			->type('Nette\Application\Responses\TextResponse')
			->getTemplate()->type('Nette\Templating\ITemplate')
			->getDom()
			->has('div[class="alert alert-warning"]')
			->hasCount(1, 'div[class="alert alert-warning"]')
			->contains('The username is incorrect.', 'div[class="alert alert-warning"]');
	}
}

\run(new LoginPresenterTest);
