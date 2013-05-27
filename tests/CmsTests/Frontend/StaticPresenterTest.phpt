<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace CmsTests\Frontend;

use CmsModule\Content\Repositories\RouteRepository;
use Nette\Application\Responses\RedirectResponse;
use Nette\Application\Responses\TextResponse;
use Nette\Templating\ITemplate;
use Tester\Assert;
use Tester\DomQuery;
use Tester\TestCase;

require __DIR__ . '/FrontendCase.php';

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class StaticPresenterTest extends FrontendCase
{

	public function testMainpage()
	{
		/** @var RouteRepository $repository */
		$repository = $this->getContainer()->getByType('CmsModule\Content\Repositories\RouteRepository');
		$route = $repository->findOneBy(array('url' => ''));

		$response = $this->getResponse('Cms:Static', 'GET', array('route' => $route));
		Assert::true($response instanceof TextResponse);
		Assert::true($response->getSource() instanceof ITemplate);
		$dom = $this->getDom($response);

		$this->assertCssContain($dom, 'Main page', 'h1');
		$this->assertXpathContain($dom, 'Main page', '//ul[@class="breadcrumb"]/li');
		$this->assertXpathContain($dom, 'Main page', '//div[starts-with(@class,"navbar")]//ul/li[starts-with(@class, "active")]/a');
		$this->assertXpathContain($dom, 'Text of main page.', '//h1/parent::div');
	}


	public function testSubpage()
	{
		/** @var RouteRepository $repository */
		$repository = $this->getContainer()->getByType('CmsModule\Content\Repositories\RouteRepository');
		$route = $repository->findOneBy(array('url' => 'subpage'));

		$response = $this->getResponse('Cms:Static', 'GET', array('route' => $route));
		Assert::true($response instanceof TextResponse);
		Assert::true($response->getSource() instanceof ITemplate);
		$dom = $this->getDom($response);

		$this->assertCssContain($dom, 'Subpage', 'h1');
		$this->assertXpathContain($dom, 'Main page', '//ul[@class="breadcrumb"]/li/a');
		$this->assertXpathContain($dom, 'Subpage', '//ul[@class="breadcrumb"]/li[2]');
		$this->assertXpathContain($dom, 'Subpage', '//div[starts-with(@class,"navbar")]//ul/li[starts-with(@class, "active")]/a');
		$this->assertXpathContain($dom, 'Text of subpage.', '//h1/parent::div');
	}

}

\run(new StaticPresenterTest);
