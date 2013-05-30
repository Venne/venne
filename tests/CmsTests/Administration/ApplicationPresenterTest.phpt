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

	private function getFormData()
	{
		return array(
			'nette' => array(
				'debugger' => array(
					'edit' => '',
					'browser' => '',
					'email' => '',
					'strictMode' => NULL,
				),
				'application' => array(
					'catchExceptions' => NULL,
					'debugger' => FALSE,
				),
				'routing' => array(
					'debugger' => FALSE,
				),
				'container' => array(
					'debugger' => FALSE,
				),
				'security' => array(
					'debugger' => FALSE,
				),
				'session' => array(
					'autoStart' => FALSE,
					'expiration' => '',
				),
				'xhtml' => NULL,
			),
			'venne' => array(
				'session' => array(
					'savePath' => '',
				),
				'stopwatch' => array(
					'debugger' => FALSE,
				),
			),
			'doctrine' => array(
				'debugger' => FALSE,
				'cacheClass' => NULL,
			),
		);
	}


	private function getFormSaveData()
	{
		return array(
			'nette' => array(
				'debugger' => array(
					'edit' => 'pepe',
					'browser' => '',
					'email' => '',
					'strictMode' => NULL,
				),
				'application' => array(
					'catchExceptions' => NULL,
					'debugger' => FALSE,
				),
				'routing' => array(
					'debugger' => FALSE,
				),
				'container' => array(
					'debugger' => FALSE,
				),
				'security' => array(
					'debugger' => FALSE,
				),
				'session' => array(
					'autoStart' => FALSE,
					'expiration' => '',
				),
				'xhtml' => NULL,
			),
			'venne' => array(
				'session' => array(
					'savePath' => '',
				),
				'stopwatch' => array(
					'debugger' => FALSE,
				),
			),
			'doctrine' => array(
				'debugger' => FALSE,
				'cacheClass' => NULL,
			),
		);
	}


	public function testActionDefault()
	{
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
			->values($this->getFormData())
			->valuesInRender($this->getFormData());
	}


	public function testActionDefaultSave()
	{
		$this->helper->prepareTestEnvironment();

		$this->helper->createResponse('Cms:Admin:Application', 'POST', array('do' => 'applicationForm-submit'), $this->getFormSaveData() + array('_submit' => 'Save'))
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
			->values($this->getFormSaveData())
			->valuesInRender($this->getFormSaveData());
	}


	public function testActionDatabase()
	{
		$this->helper->createResponse('Cms:Admin:Application', 'GET', array('action' => 'database'))
			->type('Nette\Application\Responses\TextResponse')
			->getTemplate()->type('Nette\Templating\ITemplate')
			->getDom()
			->contains('Application settings', 'h1')
			->xpathContains('Dashboard', '//div[@id="snippet--header"]/ul/li/a')
			->xpathContains('Application settings', '//div[@id="snippet--header"]/ul/li[@class="active"]');
	}


	public function testActionAccount()
	{
		$this->helper->createResponse('Cms:Admin:Application', 'GET', array('action' => 'account'))
			->type('Nette\Application\Responses\TextResponse')
			->getTemplate()->type('Nette\Templating\ITemplate')
			->getDom()
			->contains('Application settings', 'h1')
			->xpathContains('Dashboard', '//div[@id="snippet--header"]/ul/li/a')
			->xpathContains('Application settings', '//div[@id="snippet--header"]/ul/li[@class="active"]');
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
