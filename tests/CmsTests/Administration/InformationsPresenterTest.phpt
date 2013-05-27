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
class InformationsPresenterTest extends AdministrationCase
{

	public function testBasicTags()
	{
		$response = $this->getResponse('Cms:Admin:Informations', 'GET', array());
		Assert::true($response instanceof TextResponse);
		Assert::true($response->getSource() instanceof ITemplate);
		$dom = $this->getDom($response);

		$this->assertCssContain($dom, 'Website settings', 'h1');
		$this->assertXpathContain($dom, 'Dashboard', '//div[@id="snippet--header"]/ul/li/a');
		$this->assertXpathContain($dom, 'Website settings', '//div[@id="snippet--header"]/ul/li[@class="active"]');

		$fileds = array(
			'name' => 'Blog',
			'title' => '%n %s %t',
			'titleSeparator' => '|',
			'keywords' => '',
			'description' => '',
			'author' => '',
		);

		foreach ($fileds as $key => $val) {
			Assert::true($dom->has('input[name="' . $key . '"]'));
			$this->assertXpathContainAttribute($dom, $val, '//input[@name="' . $key . '"]', 'value');
		}
	}


	public function testSave()
	{
		$this->prepareTestEnvironment();

		$fileds = array(
			'name' => 'a',
			'title' => 'b',
			'titleSeparator' => 'c',
			'keywords' => 'd',
			'description' => 'e',
			'author' => 'f',
		);

		$response = $this->getResponse('Cms:Admin:Informations', 'POST', array('do' => 'websiteForm-submit'), $fileds + array('_submit' => 'Save'));
		Assert::type('Nette\Application\Responses\RedirectResponse', $response);
		Assert::contains('http:///admininformations?', $response->url);

		$this->reloadContainer();

		$response = $this->getResponse('Cms:Admin:Informations', 'GET', array());
		Assert::true($response instanceof TextResponse);
		Assert::true($response->getSource() instanceof ITemplate);
		$dom = $this->getDom($response);

		foreach ($fileds as $key => $val) {
			Assert::true($dom->has('input[name="' . $key . '"]'));
			$this->assertXpathContainAttribute($dom, $val, '//input[@name="' . $key . '"]', 'value');
		}
	}

}

\run(new InformationsPresenterTest);
