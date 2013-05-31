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

use Nette\Utils\Arrays;
use Tester\DomQuery;

require __DIR__ . '/AdministrationCase.php';

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class ApplicationPresenterTest extends AdministrationCase
{


	public function testActionDefault()
	{
		$data = include __DIR__ . '/data/Application.default.php';

		$response = $this->helper->createResponse('Cms:Admin:Application', 'GET');
		$response
			->type('Nette\Application\Responses\TextResponse')
			->getTemplate()->type('Nette\Templating\ITemplate')
			->getDom()
			->contains('Application settings', 'h1')
			->xpathContains('Dashboard', '//div[@id="snippet--header"]/ul/li/a')
			->xpathContains('Application settings', '//div[@id="snippet--header"]/ul/li[@class="active"]');
		$response
			->getPresenter()
			->hasComponent('applicationForm')
			->getForm('applicationForm')
			->values($data)
			->valuesInRender($data);

		$data = include __DIR__ . '/data/Application.default.save.php';
		$this->helper->prepareTestEnvironment();

		$this->helper->createResponse('Cms:Admin:Application', 'POST', array('do' => 'applicationForm-submit'), $data + array('_submit' => 'Save'))
			->type('Nette\Application\Responses\RedirectResponse')
			->redirectContains('http:///adminapplication?');

		$this->helper->reloadContainer();

		$response = $this->helper->createResponse('Cms:Admin:Application', 'GET', array());
		$response
			->type('Nette\Application\Responses\TextResponse')
			->getTemplate()->type('Nette\Templating\ITemplate');
		$response
			->getPresenter()
			->hasComponent('applicationForm')
			->getForm('applicationForm')
			->valid()
			->values($data)
			->valuesInRender($data);
	}


	public function testActionDatabase()
	{
		$data = include __DIR__ . '/data/Application.database.php';

		$response = $this->helper->createResponse('Cms:Admin:Application', 'GET', array('action' => 'database'));
		$response
			->type('Nette\Application\Responses\TextResponse')
			->getTemplate()->type('Nette\Templating\ITemplate')
			->getDom()
			->contains('Application settings', 'h1')
			->xpathContains('Dashboard', '//div[@id="snippet--header"]/ul/li/a')
			->xpathContains('Application settings', '//div[@id="snippet--header"]/ul/li[@class="active"]');

		$response
			->getPresenter()
			->hasComponent('databaseForm')
			->getForm('databaseForm')
			->values($data)
			->valuesInRender($data);

		$data = include __DIR__ . '/data/Application.database.save.php';
		$this->helper->prepareTestEnvironment();

		$this->helper->createResponse('Cms:Admin:Application', 'POST', array('action' => 'database', 'do' => 'databaseForm-submit'), $data + array('_submit' => 'Save'))
			->type('Nette\Application\Responses\RedirectResponse')
			->redirectContains('http:///adminapplication/database?');

		$this->helper->reloadContainer();

		$response = $this->helper->createResponse('Cms:Admin:Application', 'GET', array('action' => 'database'));
		$response
			->type('Nette\Application\Responses\TextResponse')
			->getTemplate()->type('Nette\Templating\ITemplate');
		$response
			->getPresenter()
			->hasComponent('databaseForm')
			->getForm('databaseForm')
			->valid()
			->values($data)
			->valuesInRender($data);
	}


	public function testActionAccount()
	{
		$data = include __DIR__ . '/data/Application.account.php';

		$response = $this->helper->createResponse('Cms:Admin:Application', 'GET', array('action' => 'account'));
		$response
			->type('Nette\Application\Responses\TextResponse')
			->getTemplate()->type('Nette\Templating\ITemplate')
			->getDom()
			->contains('Application settings', 'h1')
			->xpathContains('Dashboard', '//div[@id="snippet--header"]/ul/li/a')
			->xpathContains('Application settings', '//div[@id="snippet--header"]/ul/li[@class="active"]');

		$response
			->getPresenter()
			->hasComponent('accountForm')
			->getForm('accountForm')
			->values($data)
			->valuesInRender($data);

		$data = include __DIR__ . '/data/Application.account.save.php';
		$this->helper->prepareTestEnvironment();

		$this->helper->createResponse('Cms:Admin:Application', 'POST', array('action' => 'account', 'do' => 'accountForm-submit'), $data + array('_submit' => 'Save'))
			->type('Nette\Application\Responses\RedirectResponse')
			->redirectContains('http:///adminapplication/account?');

		$this->helper->reloadContainer();
		$data['_password'] = '';

		$response = $this->helper->createResponse('Cms:Admin:Application', 'GET', array('action' => 'account'));
		$response
			->type('Nette\Application\Responses\TextResponse')
			->getTemplate()->type('Nette\Templating\ITemplate');
		$response
			->getPresenter()
			->hasComponent('accountForm')
			->getForm('accountForm')
			->values($data)
			->valuesInRender($data);
	}


	public function testActionAdmin()
	{
		$this->helper->createResponse('Cms:Admin:Application', 'GET', array('action' => 'admin'))
			->type('Nette\Application\Responses\TextResponse')
			->getTemplate()->type('Nette\Templating\ITemplate')
			->getDom()
			->contains('Application settings', 'h1')
			->xpathContains('Dashboard', '//div[@id="snippet--header"]/ul/li/a')
			->xpathContains('Application settings', '//div[@id="snippet--header"]/ul/li[@class="active"]');
	}

}

\run(new ApplicationPresenterTest);
