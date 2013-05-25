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

use CmsModule\Administration\Presenters\DashboardPresenter;
use Nette\Application\Responses\RedirectResponse;
use Nette\Application\Responses\TextResponse;
use Nette\Templating\ITemplate;
use Tester\Assert;
use Tester\DomQuery;
use Tester\TestCase;
use Venne\Config\Configurator;

require __DIR__ . '/AdministrationCase.php';

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class UsersPresenterTest extends AdministrationCase
{

	protected $presenter = 'Cms:Admin:Users';


	public function testBasicTags()
	{
		$response = $this->getResponse('GET', array());
		Assert::true($response instanceof TextResponse);
		Assert::true($response->getSource() instanceof ITemplate);
		$dom = $this->getDom($response);

		$this->assertCssContain($dom, 'User manager', 'h1');
		$this->assertXpathContain($dom, 'Dashboard', '//div[@id="snippet--header"]/ul/li/a');
		$this->assertXpathContain($dom, 'User manager', '//div[@id="snippet--header"]/ul/li[@class="active"]');
	}

}

\run(new UsersPresenterTest);
