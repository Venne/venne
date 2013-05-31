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

require __DIR__ . '/AdministrationCase.php';

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class AccountPresenterTest extends AdministrationCase
{

	public function testActionDefault()
	{
		$this->helper->createResponse('Cms:Admin:Account', 'GET')
			->type('Nette\Application\Responses\TextResponse')
			->getTemplate()->type('Nette\Templating\ITemplate')
			->getDom()
			->contains('Account', 'h1')
			->xpathContains('Dashboard', '//div[@id="snippet--header"]/ul/li/a')
			->xpathContains('Account', '//div[@id="snippet--header"]/ul/li[@class="active"]');
	}


	public function testActionEdit()
	{
		$this->helper->createResponse('Cms:Admin:Account', 'GET', array('action' => 'edit'))
			->type('Nette\Application\Responses\TextResponse')
			->getTemplate()->type('Nette\Templating\ITemplate')
			->getDom()
			->contains('Edit', 'h1')
			->xpathContains('Dashboard', '//div[@id="snippet--header"]/ul/li/a')
			->xpathContains('Account', '//div[@id="snippet--header"]/ul/li[2]/a')
			->xpathContains('Edit', '//div[@id="snippet--header"]/ul/li[@class="active"]');
	}

}

\run(new AccountPresenterTest);
