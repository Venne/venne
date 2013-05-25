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

use CmsModule\Content\Presenters\LoginPresenter;
use Nette\Application\Responses\RedirectResponse;
use Nette\Application\Responses\TextResponse;
use Nette\Templating\ITemplate;
use Tester\Assert;
use Tester\DomQuery;
use Tester\TestCase;
use Venne\Config\Configurator;

require __DIR__ . '/../bootstrap.php';

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class LoginPresenterTest extends TestCase
{


	/** @var LoginPresenter */
	private $presenter;


	public function setUp()
	{
		$container = id(new Configurator(dirname(dirname(__DIR__)), getLoader()))->createContainer();
		$presenterFactory = $container->getByType('Nette\Application\IPresenterFactory');

		$this->presenter = $presenterFactory->createPresenter('Cms:Admin:Login');
		$this->presenter->autoCanonicalize = FALSE;
	}


	public function testPresenter()
	{
		$request = new \Nette\Application\Request('Cms:Admin:Login', 'GET', array());
		$response = $this->presenter->run($request);

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
		$request = new \Nette\Application\Request('Cms:Admin:Login', 'POST', array('do' => 'signInForm-submit'), array(
			'username' => 'admin', 'password' => 'admin', 'remember' => FALSE, '_submit' => 'Přihlásit se'
		));
		$response = $this->presenter->run($request);

		Assert::true($response instanceof RedirectResponse);
	}


	public function testBadPassword()
	{
		$request = new \Nette\Application\Request('Cms:Admin:Login', 'POST', array('do' => 'signInForm-submit'), array(
			'username' => 'admin', 'password' => 'wrong', 'remember' => FALSE, '_submit' => 'Přihlásit se'
		));
		$response = $this->presenter->run($request);

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
		$request = new \Nette\Application\Request('Cms:Admin:Login', 'POST', array('do' => 'signInForm-submit'), array(
			'username' => 'wrong', 'password' => 'wrong', 'remember' => FALSE, '_submit' => 'Přihlásit se'
		));
		$response = $this->presenter->run($request);

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
