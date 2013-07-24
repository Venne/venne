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

require __DIR__ . '/AdministrationCase.php';

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class ContentPresenterTest extends AdministrationCase
{

	public function testBasicTags()
	{
		$this->helper->createResponse('Cms:Admin:Content', 'GET')
			->type('Nette\Application\Responses\TextResponse')
			->getTemplate()->type('Nette\Templating\ITemplate')
			->getDom()
			->contains('Content', 'h1')
			->xpathContains('Dashboard', '//div[@id="snippet--header"]/ul/li/a')
			->xpathContains('Content', '//div[@id="snippet--header"]/ul/li[@class="active"]');
	}


	public function testActionCreate()
	{
		$this->helper->createResponse('Cms:Admin:Content', 'GET', array('action' => 'create', 'type' => 'CmsModule\Pages\Text\TextPageEntity'))
			->type('Nette\Application\Responses\TextResponse')
			->getTemplate()->type('Nette\Templating\ITemplate')
			->getDom()
			->contains('New page (static page)', 'h1')
			->xpathContains('Dashboard', '//div[@id="snippet--header"]/ul/li/a')
			->xpathContains('Content', '//div[@id="snippet--header"]/ul/li[2]/a')
			->xpathContains('New page', '//div[@id="snippet--header"]/ul/li[@class="active"]');
	}


	public function testProcessCreate()
	{
		$this->helper->prepareTestEnvironment();

		$this->helper->createResponse('Cms:Admin:Content', 'POST', array(
			'action' => 'create', 'type' => 'CmsModule\Pages\Text\TextPageEntity', 'do' => 'form-submit'
		), array(
			'page' => array('mainRoute' => array(
				'name' => 'foo', 'localUrl' => 'foo', 'title' => 'fooTitle', 'copyLayoutFromParent' => TRUE, 'copyLayoutToChildren' => TRUE,
				'layout' => NULL, 'childrenLayout' => NULL, 'parent' => 1, 'navigationShow' => TRUE, 'navigationOwn' => FALSE, 'navigationTitleRaw' => '',
				'contentTags' => array(),
			),),
			'_submit' => 'Save',
		))
			->type('Nette\Application\Responses\RedirectResponse')
			->redirectContains('http:///admincontent/edit?key=3');

		$this->helper->createResponse('Cms:Admin:Content', 'GET', array('action' => 'edit', 'key' => 3))
			->type('Nette\Application\Responses\TextResponse')
			->getTemplate()->type('Nette\Templating\ITemplate')
			->getDom()
			->contains('foo', 'h1')
			->xpathContains('Dashboard', '//div[@id="snippet--header"]/ul/li/a')
			->xpathContains('Content', '//div[@id="snippet--header"]/ul/li[2]/a')
			->xpathContains('Editing', '//div[@id="snippet--header"]/ul/li[@class="active"]');

		$this->helper->createResponse('Cms:Admin:Content', 'POST', array(
			'action' => 'edit', 'key' => 3, 'do' => 'formEdit-submit'
		), array('page' => array('mainRoute' => array('text' => 'fooText')), '_submit' => 'Save'));

		$this->helper->createResponse('Cms:Admin:Content', 'GET', array('action' => 'edit', 'key' => 3, 'do' => 'publish'));


		/** @var RouteRepository $repository */
		$repository = $this->helper->getContainer()->getByType('CmsModule\Content\Repositories\RouteRepository');
		$route = $repository->findOneBy(array('id' => 3));
		$extendedRoute = $this->helper->getContainer()->entityManager->getRepository($route->class)->findOneBy(array('route' => $route->id));

		$this->helper->createResponse('Cms:Pages:Text:Text', 'GET', array('route' => $extendedRoute))
			->type('Nette\Application\Responses\TextResponse')
			->getTemplate()->type('Nette\Templating\ITemplate')
			->getDom()
			->contains('foo', 'h1')
			->xpathContains('Main page', '//ul[@class="breadcrumb"]/li/a')
			->xpathContains('fooTitle', '//ul[@class="breadcrumb"]/li[2]')
			->xpathContains('fooText', '//h1/parent::div/p[2]');;
	}
}

\run(new ContentPresenterTest);
