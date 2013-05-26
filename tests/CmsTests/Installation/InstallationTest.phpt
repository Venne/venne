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

use CmsModule\Administration\Presenters\AdministratorPresenter;
use CmsModule\Module;
use CmsTests\PresenterCase;
use Doctrine\ORM\EntityManager;
use Nette\Application\IResponse;
use Nette\Application\Responses\RedirectResponse;
use Nette\Application\Responses\TextResponse;
use Nette\Config\Helpers;
use Nette\DI\Container;
use Nette\Templating\ITemplate;
use Nette\Utils\Neon;
use Tester\Assert;
use Tester\DomQuery;
use Tester\TestCase;
use Venne\Config\Configurator;
use Venne\Module\ModuleManager;

require __DIR__ . '/../PresenterCase.php';

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class InstallationTest extends PresenterCase
{

	private $container;

	private $containerSum = 100;


	public function setUp()
	{
		umask(0000);
		$c = include __DIR__ . '/sandbox.php';
		foreach ($c as $path) {
			if (!file_exists($path)) {
				mkdir($path, 0777, true);
			}
		}
		copy(__DIR__ . '/config.neon.orig', $c['configDir'] . '/config.neon');
		copy(__DIR__ . '/settings.php.orig', $c['configDir'] . '/settings.php');

		$this->container = id(new Configurator(__DIR__, getLoader()))->createContainer();
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
		$presenterFactory = $this->container->getByType('Nette\Application\IPresenterFactory');
		$presenter = $presenterFactory->createPresenter('Cms:Admin:Administrator');
		$presenter->autoCanonicalize = FALSE;
		$request = new \Nette\Application\Request('Cms:Admin:Administrator', 'GET', array());
		$response = $presenter->run($request);

		Assert::type('Nette\Application\Responses\TextResponse', $response);
		Assert::type('Nette\Templating\ITemplate', $response->getSource());

		$html = (string)$response->getSource();
		$dom = DomQuery::fromXml($html);

		$this->assertCssContain($dom, 'Administrator account', 'h1');

		Assert::true($dom->has('input[name="name"]'));
		Assert::true($dom->has('input[name="password"]'));
		Assert::true($dom->has('input[name="_password"]'));
		Assert::true($dom->has('input[name="_submit"]'));


		$presenter = $presenterFactory->createPresenter('Cms:Admin:Administrator');
		$presenter->autoCanonicalize = FALSE;
		$request = new \Nette\Application\Request('Cms:Admin:Administrator', 'POST', array('do' => 'systemAccountForm-submit'), array(
			'name' => 'admin', 'password' => 'admin', '_password' => 'admin', '_submit' => 'ok'
		));
		$response = $presenter->run($request);

		Assert::true($presenter['systemAccountForm']->isValid());
		Assert::type('Nette\Application\Responses\RedirectResponse', $response);
		Assert::equal('http:///admin?', substr($response->url, 0, 14));

		$this->reloadContainer();

		$presenterFactory = $this->container->getByType('Nette\Application\IPresenterFactory');
		$presenter = $presenterFactory->createPresenter('Cms:Admin:Dashboard');
		$presenter->autoCanonicalize = FALSE;
		$request = new \Nette\Application\Request('Cms:Admin:Dashboard', 'GET', array());
		$response = $presenter->run($request);

		Assert::type('Nette\Application\Responses\RedirectResponse', $response);
		Assert::equal('http:///admindatabase', $response->url);
	}


	public function secondPage()
	{
		$presenterFactory = $this->container->getByType('Nette\Application\IPresenterFactory');
		$presenter = $presenterFactory->createPresenter('Cms:Admin:Database');
		$presenter->autoCanonicalize = FALSE;
		$request = new \Nette\Application\Request('Cms:Admin:Database', 'GET', array());
		$response = $presenter->run($request);

		Assert::type('Nette\Application\Responses\TextResponse', $response);
		Assert::type('Nette\Templating\ITemplate', $response->getSource());
		Assert::type('Venne\Forms\Form', $presenter['systemDatabaseForm']);
		$html = (string)$response->getSource();
		$dom = DomQuery::fromXml($html);
		$this->assertCssContain($dom, 'Database settings', 'h1');

		$presenter = $presenterFactory->createPresenter('Cms:Admin:Database');
		$presenter->autoCanonicalize = FALSE;
		$request = new \Nette\Application\Request('Cms:Admin:Database', 'POST', array('do' => 'systemDatabaseForm-submit'), array(
			'driver' => 'pdo_sqlite', 'user' => '', 'password' => '', 'path' => '%tempDir%/database.db', 'charset' => 'utf8', '_submit' => 'ok'
		));
		$response = $presenter->run($request);

		Assert::true($presenter['systemDatabaseForm']->isValid());
		Assert::type('Nette\Application\Responses\RedirectResponse', $response);
		Assert::equal('http:///admindatabase?', substr($response->url, 0, 22));

		$this->reloadContainer();

		$presenterFactory = $this->container->getByType('Nette\Application\IPresenterFactory');
		$presenter = $presenterFactory->createPresenter('Cms:Admin:Database');
		$presenter->autoCanonicalize = FALSE;
		$request = new \Nette\Application\Request('Cms:Admin:Database', 'GET', array('do' => 'install'));
		$response = $presenter->run($request);

		Assert::type('Nette\Application\Responses\RedirectResponse', $response);
		Assert::equal('http:///admin', $response->url);

		$this->reloadContainer();

		$presenterFactory = $this->container->getByType('Nette\Application\IPresenterFactory');
		$presenter = $presenterFactory->createPresenter('Cms:Admin:Dashboard');
		$presenter->autoCanonicalize = FALSE;
		$request = new \Nette\Application\Request('Cms:Admin:Dashboard', 'GET', array());
		$response = $presenter->run($request);

		Assert::type('Nette\Application\Responses\RedirectResponse', $response);
		Assert::contains('http:///adminlanguage?', $response->url);
		Assert::contains('do=table-navbar-click', $response->url);
		Assert::contains('table-navbar-id=navbar-new', $response->url);
	}


	public function thirdPage()
	{
		$presenterFactory = $this->container->getByType('Nette\Application\IPresenterFactory');
		$presenter = $presenterFactory->createPresenter('Cms:Admin:Language');
		$presenter->autoCanonicalize = FALSE;
		$request = new \Nette\Application\Request('Cms:Admin:Language', 'GET', array('table-navbar-id' => 'navbar-new', 'do' => 'table-navbar-click'));
		$response = $presenter->run($request);

		Assert::type('Nette\Application\Responses\TextResponse', $response);
		Assert::type('Nette\Templating\ITemplate', $response->getSource());
		$dom = $this->getDom($response);
		Assert::true($dom->has('#frm-table-navbarForm'));

		$presenterFactory = $this->container->getByType('Nette\Application\IPresenterFactory');
		$presenter = $presenterFactory->createPresenter('Cms:Admin:Language');
		$presenter->autoCanonicalize = FALSE;
		$request = new \Nette\Application\Request('Cms:Admin:Language', 'POST', array('table-formName' => 'new', 'do' => 'table-navbarForm-submit'), array(
			'name' => 'English', 'short' => 'en', 'alias' => 'en', '_submit' => 'Save',
		));
		$response = $presenter->run($request);

		Assert::type('Venne\Forms\Form', $presenter['table-navbarForm']);
		Assert::true($presenter['table-navbarForm']->isValid());
		Assert::type('Nette\Application\Responses\RedirectResponse', $response);
		Assert::contains('http:///adminlanguage?', $response->url);

		$this->reloadContainer();

		$presenterFactory = $this->container->getByType('Nette\Application\IPresenterFactory');
		$presenter = $presenterFactory->createPresenter('Cms:Admin:Dashboard');
		$presenter->autoCanonicalize = FALSE;
		$request = new \Nette\Application\Request('Cms:Admin:Dashboard', 'GET', array());
		$response = $presenter->run($request);

		Assert::type('Nette\Application\Responses\TextResponse', $response);
		Assert::type('Nette\Templating\ITemplate', $response->getSource());
	}


	private function installModules()
	{
		/** @var ModuleManager $moduleManager */
		$moduleManager = $this->container->getByType('Venne\Module\ModuleManager');
		$moduleManager->update();
		include dirname(dirname(dirname(__DIR__))) . '/Module.php';
		$moduleManager->register(new Module);

		$c = include __DIR__ . '/sandbox.php';
		/** @var Configurator $configurator */
		$configurator = $this->container->configurator;
		$configurator->addParameters(include $c['configDir'] . '/settings.php');

		$moduleManager->install($moduleManager->createInstance('translator'), true);
		$moduleManager->install($moduleManager->createInstance('assets'), true);
		$moduleManager->install($moduleManager->createInstance('forms'), true);
		$moduleManager->install($moduleManager->createInstance('doctrine'), true);
		$moduleManager->install($moduleManager->createInstance('gedmo'), true);
		$moduleManager->install($moduleManager->createInstance('cms'), true);

		$parameters = include $c['configDir'] . '/settings.php';
		foreach ($parameters['modules'] as &$module) {
			$module['path'] = \Nette\DI\Helpers::expand($module['path'], $this->container->parameters);
		}
		$configurator->addParameters($parameters);

		$this->reloadContainer();
	}


	private function reloadContainer($reloadConfigurator = false)
	{
		$configurator = $reloadConfigurator ? new Configurator(__DIR__, getLoader()) : $this->container->configurator;

		$class = $this->container->parameters['container']['class'] . $this->containerSum++;
		\Nette\Utils\LimitedScope::evaluate($configurator->buildContainer($dependencies, $class));
		$this->container = new $class;
		//$this->container->parameters = Helpers::merge($parameters, $this->container->parameters);
		$this->container->initialize();
		$this->container->addService('configurator', $configurator);
	}

}

\run(new InstallationTest);
