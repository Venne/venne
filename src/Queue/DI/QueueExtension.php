<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Queue\DI;

use Kdyby\Doctrine\DI\IEntityProvider;
use Nette\DI\CompilerExtension;
use Nette\DI\Statement;
use Nette\PhpGenerator\PhpLiteral;
use Venne\System\DI\IPresenterProvider;
use Venne\System\DI\SystemExtension;
use Venne\Widgets\DI\WidgetsExtension;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class QueueExtension extends CompilerExtension implements IEntityProvider, IPresenterProvider
{

	/** @var array */
	public $config = array(
		'interval' => 20,
		'configDir' => '%appDir%/../temp/worker'
	);


	public function loadConfiguration()
	{
		$container = $this->getContainerBuilder();
		$config = $this->getConfig($this->config);

		$container->addDefinition($this->prefix('configManager'))
			->setClass('Venne\Queue\ConfigManager', array($container->expand($config['configDir'])));

		$container->addDefinition($this->prefix('workerManager'))
			->setClass('Venne\Queue\WorkerManager', array($config['interval']));

		$jobManager = $container->addDefinition($this->prefix('jobManager'))
			->setClass('Venne\Queue\JobManager', array(new Statement('@doctrine.dao', array('Venne\Queue\JobEntity'))));

		foreach ($this->compiler->getExtensions() as $extension) {
			if ($extension instanceof IJobProvider) {
				foreach ($extension->getJobClasses() as $class) {
					$jobManager->addSetup('$service->registerJob(?)', array($class));
				}
			}
		}

		$container->addDefinition($this->prefix('defaultPresenter'))
			->setClass('Venne\Queue\AdminModule\DefaultPresenter')
			->addTag(SystemExtension::TAG_ADMINISTRATION, array(
				'link' => 'Queue:Admin:Default:',
				'category' => 'System',
				'name' => 'Worker manager',
				'description' => 'Manage workers',
				'priority' => 10,
			));

		$container->addDefinition($this->prefix('jobsPresenter'))
			->setClass('Venne\Queue\AdminModule\JobsPresenter', array(new Statement('@doctrine.dao', array('Venne\Queue\JobEntity'))));

		$container->addDefinition($this->prefix('workerFactory'))
			->setImplement('Venne\Queue\IWorkerFactory')
			->setArguments(array(new PhpLiteral('$id'), new PhpLiteral('$interval'), $container->expand($config['configDir'])))
			->setAutowired(TRUE);

		$container->addDefinition($this->prefix('jobFormFactory'))
			->setClass('Venne\Queue\AdminModule\JobFormFactory', array(new Statement('@system.admin.basicFormFactory')));

		$container->addDefinition($this->prefix('jobsControlFactory'))
			->setArguments(array(new Statement('@doctrine.dao', array('Venne\Queue\JobEntity'))))
			->setImplement('Venne\Queue\Components\IJobsControlFactory')
			->setInject(TRUE)
			->addTag(SystemExtension::TAG_TRAY_COMPONENT)
			->addTag(WidgetsExtension::TAG_WIDGET,'jobs');

		$container->addDefinition($this->prefix('jobControlFactory'))
			->setArguments(array(new Statement('@doctrine.dao', array('Venne\Queue\JobEntity'))))
			->setImplement('Venne\Queue\Components\IJobControlFactory')
			->setInject(TRUE);
	}


	/**
	 * @return array
	 */
	public function getEntityMappings()
	{
		return array(
			'Venne\Queue' => dirname(__DIR__) . '/*Entity.php',
		);
	}


	/**
	 * @return array
	 */
	public function getPresenterMapping()
	{
		return array(
			'Queue' => 'Venne\Queue\*Module\*Presenter',
		);
	}

}
