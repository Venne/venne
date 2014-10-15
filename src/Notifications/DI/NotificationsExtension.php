<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Notifications\DI;

use Nette\DI\Statement;
use Venne\System\DI\SystemExtension;
use Venne\Widgets\DI\WidgetsExtension;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class NotificationsExtension extends \Nette\DI\CompilerExtension implements
	\Venne\Queue\DI\IJobProvider,
	\Kdyby\Doctrine\DI\IEntityProvider,
	\Venne\System\DI\IPresenterProvider
{

	/** @var mixed[] */
	public $defaults = array(
		'mailer' => array(
			'senderEmail' => 'info@venne.cz',
			'senderName' => 'Venne',
		),
	);

	public function loadConfiguration()
	{
		$container = $this->getContainerBuilder();
		$this->compiler->parseServices(
			$container,
			$this->loadFromFile(__DIR__ . '/services.neon')
		);
		$config = $this->getConfig($this->defaults);

		$notificationManager = $container->addDefinition($this->prefix('notificationManager'))
			->setClass('Venne\Notifications\NotificationManager');

		$container->addDefinition($this->prefix('emailManager'))
			->setClass('Venne\Notifications\EmailManager', array(
				$config['mailer']['senderEmail'],
				$config['mailer']['senderName'],
			));

		foreach ($this->compiler->extensions as $extension) {
			if ($extension instanceof IEventProvider) {
				foreach ($extension->getEventTypes() as $type) {
					$notificationManager->addSetup('$service->addType(?)', array(new Statement($type, array())));
				}
			}
		}

		$container->addDefinition($this->prefix('notificationControl'))
			->setImplement('Venne\Notifications\Components\INotificationControlFactory')
			->setInject(true);

		$container->addDefinition($this->prefix('notificationsControl'))
			->setImplement('Venne\Notifications\Components\INotificationsControlFactory')
			->addTag(SystemExtension::TAG_TRAY_COMPONENT)
			->addTag(WidgetsExtension::TAG_WIDGET, 'notifications')
			->setInject(true);

		$container->addDefinition($this->prefix('settingsPresenter'))
			->setClass('Venne\Notifications\AdminModule\SettingsPresenter');

		// Jobs
		$container->addDefinition($this->prefix('notificationJob'))
			->setClass('Venne\Notifications\Jobs\NotificationJob');

		$container->addDefinition($this->prefix('emailJob'))
			->setClass('Venne\Notifications\Jobs\EmailJob');
	}

	public function beforeCompile()
	{
		$container = $this->getContainerBuilder();
		$extensions = $this->compiler->getExtensions('Kdyby\Events\DI\EventsExtension');

		$evm = $container->getDefinition(reset($extensions)->prefix('manager'));
		$evm->setClass('Venne\Notifications\EventManager', $evm->factory->arguments);
	}

	/**
	 * @return string[]
	 */
	public function getJobClasses()
	{
		return array(
			'Venne\Notifications\Jobs\EmailJob',
			'Venne\Notifications\Jobs\NotificationJob',
		);
	}

	/**
	 * @return string[]
	 */
	public function getPresenterMapping()
	{
		return array(
			'Admin:Notifications' => 'Venne\*\AdminModule\*Presenter',
		);
	}

	/**
	 * @return string[]
	 */
	public function getEntityMappings()
	{
		return array(
			'Venne\Notifications' => dirname(__DIR__) . '/*.php',
		);
	}

}
