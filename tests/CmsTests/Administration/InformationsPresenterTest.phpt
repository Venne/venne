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
class InformationsPresenterTest extends AdministrationCase
{

	protected function getFormData()
	{
		return array(
			'name' => 'Blog',
			'title' => '%n %s %t',
			'titleSeparator' => '|',
			'keywords' => '',
			'description' => '',
			'author' => '',
			'cacheMode' => NULL,
			'cacheValue' => '10',
			'routePrefix' => '',
			'oneWayRoutePrefix' => '',
		);
	}


	protected function getFormSaveData()
	{
		return array(
			'name' => 'a',
			'title' => 'b',
			'titleSeparator' => 'c',
			'keywords' => 'd',
			'description' => 'e',
			'author' => 'f',
			'cacheMode' => 'time',
			'cacheValue' => '20',
			'routePrefix' => 'o',
			'oneWayRoutePrefix' => 'p',
		);
	}


	public function testBasicTags()
	{
		$response = $this->helper->createResponse('Cms:Admin:Informations', 'GET', array());
		$response
			->type('Nette\Application\Responses\TextResponse')
			->getTemplate()->type('Nette\Templating\ITemplate')
			->getDom()
			->contains('Website settings', 'h1')
			->xpathContains('Dashboard', '//div[@id="snippet--header"]/ul/li/a')
			->xpathContains('Website settings', '//div[@id="snippet--header"]/ul/li[@class="active"]');
		$response
			->getPresenter()
			->hasComponent('websiteForm')
			->getForm('websiteForm')
			->values($this->getFormData())
			->valuesInRender($this->getFormData());
	}


	public function testSave()
	{
		$this->helper->prepareTestEnvironment();

		$this->helper->createResponse('Cms:Admin:Informations', 'POST', array('do' => 'websiteForm-submit'), $this->getFormSaveData() + array('_submit' => 'Save'))
			->type('Nette\Application\Responses\RedirectResponse')
			->redirectContains('http:///admininformations?');

		$this->helper->reloadContainer();

		$response = $this->helper->createResponse('Cms:Admin:Informations', 'GET', array());
		$response
			->type('Nette\Application\Responses\TextResponse')
			->getTemplate()->type('Nette\Templating\ITemplate');
		$response
			->getPresenter()
			->hasComponent('websiteForm')
			->getForm('websiteForm')
			->values($this->getFormSaveData())
			->valuesInRender($this->getFormSaveData());
	}

}

\run(new InformationsPresenterTest);
