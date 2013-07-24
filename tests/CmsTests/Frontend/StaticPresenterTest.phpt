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

require __DIR__ . '/FrontendCase.php';

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class StaticPresenterTest extends FrontendCase
{

	public function testMainpage()
	{
		/** @var RouteRepository $repository */
		$repository = $this->helper->getContainer()->getByType('CmsModule\Content\Repositories\RouteRepository');
		$route = $repository->findOneBy(array('url' => ''));
		$extendedRoute = $this->helper->getContainer()->entityManager->getRepository($route->class)->findOneBy(array('route' => $route->id));

		$this->helper->createResponse('Cms:Pages:Text:Text', 'GET', array('route' => $extendedRoute))
			->type('Nette\Application\Responses\TextResponse')
			->getTemplate()->type('Nette\Templating\ITemplate')
			->getDom()
			->contains('Main page', 'h1')
			->xpathContains('Main page', '//ul[@class="breadcrumb"]/li')
			->xpathContains('Main page', '//div[starts-with(@class,"navbar")]//ul/li[starts-with(@class, "active")]/a')
			->xpathContains('Text of main page.', '//h1/parent::div/p[2]');
	}


	public function testSubpage()
	{
		/** @var RouteRepository $repository */
		$repository = $this->helper->getContainer()->getByType('CmsModule\Content\Repositories\RouteRepository');
		$route = $repository->findOneBy(array('url' => 'subpage'));
		$extendedRoute = $this->helper->getContainer()->entityManager->getRepository($route->class)->findOneBy(array('route' => $route->id));

		$this->helper->createResponse('Cms:Pages:Text:Text', 'GET', array('route' => $extendedRoute))
			->type('Nette\Application\Responses\TextResponse')
			->getTemplate()->type('Nette\Templating\ITemplate')
			->getDom()
			->contains('Subpage', 'h1')
			->xpathContains('Main page', '//ul[@class="breadcrumb"]/li/a')
			->xpathContains('Subpage', '//ul[@class="breadcrumb"]/li[2]')
			->xpathContains('Subpage', '//div[starts-with(@class,"navbar")]//ul/li[starts-with(@class, "active")]/a')
			->xpathContains('Text of subpage.', '//h1/parent::div/p[2]');
	}
}

\run(new StaticPresenterTest);
