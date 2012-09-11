<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace CmsModule\DI;

use Venne;
use Venne\Config\CompilerExtension;
use Nette\Application\Routers\Route;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class CmsExtension extends CompilerExtension
{

	public $defaults = array(
		'stopwatch' => array(
			'debugger' => NULL
		),

	);


	/**
	 * Processes configuration data. Intended to be overridden by descendant.
	 * @return void
	 */
	public function loadConfiguration()
	{
		parent::loadConfiguration();
		$container = $this->getContainerBuilder();
		$config = $this->getConfig($this->defaults);

		$container->addDependency($container->parameters["tempDir"] . "/installed");

		// http
		$httpResponse = $container->getDefinition('httpResponse');
		foreach ($httpResponse->setup as $setup) {
			if ($setup->entity == 'setHeader' && $setup->arguments[0] == 'X-Powered-By') {
				$httpResponse->addSetup('setHeader', array('X-Powered-By', $setup->arguments[1] . ' && Venne:CMS'));
			}
		}

		// Application
		$application = $container->getDefinition('application');
		$application->addSetup('$service->errorPresenter = ?', $container->parameters['website']['errorPresenter']);

		$container->addDefinition("authorizatorFactory")
			->setFactory("CmsModule\Security\AuthorizatorFactory", array('@nette.presenterFactory', '@cms.roleRepository', '@session', '@doctrine.checkConnectionFactory'))
			->addSetup('setReader');

		$container->addDefinition("authorizator")
			->setClass("Nette\Security\Permission")
			->setFactory("@authorizatorFactory::getPermissionsByUser", array('@user', true));

		$container->addDefinition("authenticator")
			->setClass("CmsModule\Security\Authenticator", array("%administration.login.name%", "%administration.login.password%", "@doctrine.checkConnectionFactory", "@cms.userRepository"));

		// detect prefix
		$prefix = $container->parameters["website"]["routePrefix"];
		$adminPrefix = $container->parameters["administration"]["routePrefix"];
		$languages = $container->parameters["website"]["languages"];
		$prefix = str_replace('<lang>/', '<lang ' . implode('|', $languages) . '>/', $prefix);

		// parameters
		$parameters = array();
		$parameters["lang"] = count($languages) > 1 || $container->parameters["website"]["routePrefix"] ? NULL : $container->parameters["website"]["defaultLanguage"];

		// Sitemap
		$container->addDefinition($this->prefix("robotsRoute"))
			->setClass("Nette\Application\Routers\Route", array('robots.txt',
			array('presenter' => 'Cms:Sitemap', 'action' => 'robots', 'lang' => NULL)
		))
			->addTag("route", array("priority" => 999999999));
		$container->addDefinition($this->prefix("sitemapRoute"))
			->setClass("Nette\Application\Routers\Route", array('<lang>/sitemap.xml',
			array('presenter' => 'Cms:Sitemap', 'action' => 'sitemap',)
		))
			->addTag("route", array("priority" => 999999998));

		// Administration
		$container->addDefinition($this->prefix("adminRoute"))
			->setClass("Nette\Application\Routers\Route", array($adminPrefix . '[' . ($adminPrefix ? '/' : '') . '<lang>/]<presenter>[/<action>[/<id>]]',
			array('module' => 'Cms:Admin', 'presenter' => $container->parameters['administration']['defaultPresenter'], 'action' => 'default',)
		))
			->addTag("route", array("priority" => 100000));

		// installation
		if (!$container->parameters['administration']['login']['name']) {
			$container->addDefinition($this->prefix("installationRoute"))
				->setClass("Nette\Application\Routers\Route", array('', "Cms:Admin:{$container->parameters['administration']['defaultPresenter']}:", Route::ONE_WAY))
				->addTag("route", array("priority" => -1));
		}

		// CMS route
		$container->addDefinition($this->prefix("pageRoute"))
			->setClass("CmsModule\Content\Routes\PageRoute", array('@doctrine.checkConnectionFactory', '@cms.contentManager', '@cms.routeRepository', '@cms.languageRepository', $prefix, $parameters, $container->parameters["website"]["languages"], $container->parameters["website"]["defaultLanguage"])
		)
			->addTag("route", array("priority" => 100));

		// File route
		$container->addDefinition($this->prefix("fileRoute"))
			->setClass("CmsModule\Content\Routes\FileRoute", array('@cms.fileRepository')
		)
			->addTag("route", array("priority" => 99999999));

		// config manager
		$container->addDefinition($this->prefix("configService"))
			->setClass("CmsModule\Services\ConfigBuilder", array("%configDir%/config.neon"))
			->addTag("service");

		// Stopwatch
		if ($config['stopwatch']['debugger']) {
			$application = $container->getDefinition('application');
			$application->addSetup('Nette\Diagnostics\Debugger::$bar->addPanel(?)', array(
				new \Nette\DI\Statement('CmsModule\Panels\Stopwatch')
			));
		}
	}


	public function beforeCompile()
	{
		$this->registerContentTypes();
		$this->registerAdministrationPages();
		$this->registerElements();
	}


	protected function registerContentTypes()
	{
		$container = $this->getContainerBuilder();
		$manager = $container->getDefinition('cms.contentManager');

		foreach ($container->findByTag('contentType') as $item => $tags) {
			$arguments = $container->getDefinition($item)->factory->arguments;
			$entityName = '\\' . $arguments[0];
			$type = $entityName::getType();

			$container->getDefinition($item)->factory->arguments = array(
				0 => $type,
				1 => $tags['name'],
				2 => $arguments[0],
			);

			$manager->addSetup('$service->addContentType(?, ?, ?)', $type, $tags['name'], "@{$item}");
		}
	}


	protected function registerAdministrationPages()
	{
		/** @var $container \Nette\DI\ContainerBuilder */
		$container = $this->getContainerBuilder();
		$manager = $container->getDefinition('cms.administrationManager');

		foreach ($this->getSortedServices('administration') as $item) {
			$tags = $container->getDefinition($item)->tags['administration'];
			$manager->addSetup('addAdministrationPage', array($tags['name'], $tags['description'], $tags['category'], $tags['link']));
		}
	}


	protected function registerElements()
	{
		$container = $this->getContainerBuilder();
		$config = $container->getDefinition('venne.widgetManager');

		foreach ($container->findByTag('element') as $factory => $meta) {
			$definition = $container->getDefinition($factory);

			if (!is_string($meta)) {
				throw new \Nette\InvalidArgumentException("Tag element require name. Provide it in configuration. (tags: [element: name])");
			}

			$config->addSetup('addWidget', array(\CmsModule\Content\ElementManager::ELEMENT_PREFIX . $meta, "@{$factory}"));
		}
	}
}
