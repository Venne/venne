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

use Nette\Application\Responses\RedirectResponse;
use Nette\Application\Responses\TextResponse;
use Nette\Templating\ITemplate;
use Tester\Assert;
use Tester\DomQuery;
use Tester\TestCase;

require __DIR__ . '/AdministrationCase.php';

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class LanguagePresenterTest extends AdministrationCase
{

	public function testBasicTags()
	{
		$response = $this->getResponse('Cms:Admin:Language', 'GET', array());
		Assert::true($response instanceof TextResponse);
		Assert::true($response->getSource() instanceof ITemplate);
		$dom = $this->getDom($response);

		$this->assertCssContain($dom, 'Language settings', 'h1');
		$this->assertXpathContain($dom, 'Dashboard', '//div[@id="snippet--header"]/ul/li/a');
		$this->assertXpathContain($dom, 'Language settings', '//div[@id="snippet--header"]/ul/li[@class="active"]');
	}

}

\run(new LanguagePresenterTest);
