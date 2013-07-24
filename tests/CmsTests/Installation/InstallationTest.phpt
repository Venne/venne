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

use CmsModule\Module;
use Doctrine\ORM\EntityManager;
use Venne\Module\ModuleManager;
use Venne\Tester\TestCase;

require __DIR__ . '/../bootstrap.php';

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class InstallationTest extends TestCase
{

	public function setUp()
	{
		$c = include __DIR__ . '/sandbox.php';
		foreach ($c as $path) {
			if (!file_exists($path)) {
				mkdir($path, 0777, true);
			}
		}
		copy(__DIR__ . '/config.neon.orig', $c['configDir'] . '/config.neon');
		copy(__DIR__ . '/settings.php.orig', $c['configDir'] . '/settings.php');

		$this->helper->setSandboxDir(__DIR__);
	}


	public function testAll()
	{
		$this->installModules();
		$this->firstPage();
		$this->secondPage();
		$this->thirdPage();
	}


	public function firstPage()
	{
		$this->helper->createResponse('Cms:Admin:Administrator', 'GET', array())
			->type('Nette\Application\Responses\TextResponse')
			->getTemplate()->type('Nette\Templating\ITemplate')
			->getDom()
			->contains('Administrator account', 'h1')
			->has('input[name="name"]')
			->has('input[name="password"]')
			->has('input[name="_password"]')
			->has('input[name="_submit"]');

		$this->helper->createResponse('Cms:Admin:Administrator', 'POST', array('do' => 'systemAccountForm-submit'), array(
			'name' => 'admin', 'password' => 'admin', '_password' => 'admin', '_submit' => 'ok'
		))
			->type('Nette\Application\Responses\RedirectResponse')
			->redirectContains('http:///admin?');

		$this->helper->reloadContainer();

		$this->helper->createResponse('Cms:Admin:Dashboard', 'GET', array())
			->type('Nette\Application\Responses\RedirectResponse')
			->redirectContains('http:///admindatabase');
	}


	public function secondPage()
	{
		$this->helper->createResponse('Cms:Admin:Database', 'GET', array())
			->type('Nette\Application\Responses\TextResponse')
			->getTemplate()->type('Nette\Templating\ITemplate')
			->getDom()
			->contains('Database settings', 'h1');

		$this->helper->createResponse('Cms:Admin:Database', 'POST', array('do' => 'systemDatabaseForm-submit'), array(
			'driver' => 'pdo_sqlite', 'user' => '', 'password' => '', 'path' => '%tempDir%/database.db', 'charset' => 'utf8', '_submit' => 'ok'
		))
			->type('Nette\Application\Responses\RedirectResponse')
			->redirectContains('http:///admindatabase?');

		$this->helper->reloadContainer();

		$this->helper->createResponse('Cms:Admin:Database', 'GET', array('do' => 'install'))
			->type('Nette\Application\Responses\RedirectResponse')
			->redirectContains('http:///admin');

		$this->helper->reloadContainer();

		$this->helper->createResponse('Cms:Admin:Dashboard', 'GET', array())
			->type('Nette\Application\Responses\RedirectResponse')
			->redirectContains('http:///adminlanguage?')
			->redirectContains('do=table-navbar-click')
			->redirectContains('table-navbar-id=navbar-new');
	}


	public function thirdPage()
	{
		$this->helper->createResponse('Cms:Admin:Language', 'GET', array('table-navbar-id' => 'navbar-new', 'do' => 'table-navbar-click'))
			->type('Nette\Application\Responses\TextResponse')
			->getTemplate()->type('Nette\Templating\ITemplate')
			->getDom()
			->has('#frm-table-navbarForm');

		$this->helper->createResponse('Cms:Admin:Language', 'POST', array('table-formName' => 'new', 'do' => 'table-navbarForm-submit'), array(
			'name' => 'English', 'short' => 'en', 'alias' => 'en', '_submit' => 'Save',
		))
			->type('Nette\Application\Responses\RedirectResponse')
			->redirectContains('http:///adminlanguage');

		$this->helper->reloadContainer();

		$this->helper->createResponse('Cms:Admin:Dashboard', 'GET', array())
			->type('Nette\Application\Responses\TextResponse')
			->getTemplate()->type('Nette\Templating\ITemplate');
	}


	private function installModules()
	{
		/** @var ModuleManager $moduleManager */
		$moduleManager = $this->helper->getContainer()->getByType('Venne\Module\ModuleManager');
		$moduleManager->update();
		include_once dirname(dirname(dirname(__DIR__))) . '/Module.php';
		$moduleManager->register(new Module);

		$c = include __DIR__ . '/sandbox.php';
		/** @var Configurator $configurator */
		$configurator = $this->helper->getContainer()->configurator;
		$configurator->addParameters(include $c['configDir'] . '/settings.php');

		$moduleManager->install($moduleManager->createInstance('translator'), true);
		$moduleManager->install($moduleManager->createInstance('assets'), true);
		$moduleManager->install($moduleManager->createInstance('forms'), true);
		$moduleManager->install($moduleManager->createInstance('doctrine'), true);
		$moduleManager->install($moduleManager->createInstance('cms'), true);

		$parameters = include $c['configDir'] . '/settings.php';
		foreach ($parameters['modules'] as &$module) {
			$module['path'] = \Nette\DI\Helpers::expand($module['path'], $this->helper->getContainer()->parameters);
		}
		$configurator->addParameters($parameters);

		$this->helper->reloadContainer();
	}

}

\run(new InstallationTest);
