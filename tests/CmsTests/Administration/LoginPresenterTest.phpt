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
use Nette\Application\Responses\RedirectResponse;
use Nette\Application\Responses\TextResponse;
use Nette\Templating\ITemplate;
use Tester\Assert;
use Tester\DomQuery;

require __DIR__ . '/AdministrationCase.php';

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class LoginPresenterTest extends PresenterCase
{

	public function testPresenter()
	{
		$response = $this->getResponse('Cms:Admin:Login', 'GET', array());

		Assert::true($response instanceof TextResponse);
		Assert::true($response->getSource() instanceof ITemplate);

		$html = (string)$response->getSource();
		$dom = DomQuery::fromXml($html);

		Assert::true($dom->has('input[name="username"]'));
		Assert::true($dom->has('input[name="password"]'));
		Assert::true($dom->has('input[name="remember"]'));
		Assert::true($dom->has('input[name="_submit"]'));
	}


	public function testLogin()
	{
		$response = $this->getResponse('Cms:Admin:Login', 'POST', array('do' => 'signInForm-submit'), array(
			'username' => 'admin', 'password' => 'admin', 'remember' => FALSE, '_submit' => 'Přihlásit se'
		));

		Assert::true($response instanceof RedirectResponse);
	}


	public function testBadPassword()
	{
		$response = $this->getResponse('Cms:Admin:Login', 'POST', array('do' => 'signInForm-submit'), array(
			'username' => 'admin', 'password' => 'wrong', 'remember' => FALSE, '_submit' => 'Přihlásit se'
		));

		Assert::true($response instanceof TextResponse);
		Assert::true($response->getSource() instanceof ITemplate);

		$html = (string)$response->getSource();
		$dom = DomQuery::fromXml($html);

		Assert::true($dom->has('div[class="alert alert-warning"]'));

		$el = $dom->find('div[class="alert alert-warning"]');
		Assert::equal(1, count($el));
		Assert::equal('The password is incorrect.', trim((string)$el[0]));
	}


	public function testBadUsername()
	{
		$response = $this->getResponse('Cms:Admin:Login', 'POST', array('do' => 'signInForm-submit'), array(
			'username' => 'wrong', 'password' => 'wrong', 'remember' => FALSE, '_submit' => 'Přihlásit se'
		));

		Assert::true($response instanceof TextResponse);
		Assert::true($response->getSource() instanceof ITemplate);

		$html = (string)$response->getSource();
		$dom = DomQuery::fromXml($html);

		Assert::true($dom->has('div[class="alert alert-warning"]'));

		$el = $dom->find('div[class="alert alert-warning"]');
		Assert::equal(1, count($el));
		Assert::equal('The username is incorrect.', trim((string)$el[0]));
	}
}

\run(new LoginPresenterTest);
