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

use Kdyby\Doctrine\DI\IEntityProvider;
use Nette\Application\Routers\Route;
use Nette\DI\CompilerExtension;
use Nette\DI\Statement;
use Venne\Queue\DI\IJobProvider;
use Venne\System\DI\IPresenterProvider;
use Venne\System\DI\SystemExtension;
use Venne\Widgets\DI\WidgetsExtension;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class NotificationsExtension extends CompilerExtension implements IJobProvider, IEntityProvider, IPresenterProvider
{

	/** @var array */
	public $defaults = array(
		'mailer' => array(
			'senderEmail' => 'info@venne.cz',
			'senderName' => 'Venne',
		),
	);


	public function loadConfiguration()
	{
		$container = $this->getContainerBuilder();
		$config = $this->getConfig($this->defaults);

		$notificationManager = $container->addDefinition($this->prefix('notificationManager'))
			->setClass('Venne\Notifications\NotificationManager', array(
				new Statement('@doctrine.dao', array('Venne\Notifications\NotificationEntity')),
				new Statement('@doctrine.dao', array('Venne\Notifications\NotificationUserEntity')),
				new Statement('@doctrine.dao', array('Venne\Notifications\NotificationSettingEntity')),
				new Statement('@doctrine.dao', array('Venne\Notifications\NotificationTypeEntity')),
				new Statement('@doctrine.dao', array('Venne\Security\UserEntity'))
			));

		$container->addDefinition($this->prefix('emailManager'))
			->setClass('Venne\Notifications\EmailManager', array(
				$config['mailer']['senderEmail'],
				$config['mailer']['senderName'],
			));

		foreach ($this->compiler->extensions as $extension) {
			if ($extension instanceof IEventProvider) {
				foreach ($extension->getEventTypes() as $type)
					$notificationManager->addSetup('$service->addType(?)', array(new Statement($type, array())));
			}
		}


		$container->addDefinition($this->prefix('notificationControl'))
			->setArguments(array(
				new Statement('@doctrine.dao', array('Venne\Notifications\NotificationUserEntity'))
			))
			->setImplement('Venne\Notifications\Components\INotificationControlFactory')
			->setInject(TRUE);

		$container->addDefinition($this->prefix('notificationsControl'))
			->setArguments(array(
				new Statement('@doctrine.dao', array('Venne\Notifications\NotificationUserEntity'))
			))
			->setImplement('Venne\Notifications\Components\INotificationsControlFactory')
			->addTag(SystemExtension::TAG_TRAY_COMPONENT)
			->addTag(WidgetsExtension::TAG_WIDGET, 'notifications')
			->setInject(TRUE);

		$container->addDefinition($this->prefix('settingsPresenter'))
			->setClass('Venne\Notifications\AdminModule\SettingsPresenter', array(new Statement('@doctrine.dao', array('Venne\Notifications\NotificationSettingEntity'))));

		// Jobs
		$container->addDefinition($this->prefix('notificationJob'))
			->setClass('Venne\Notifications\Jobs\NotificationJob', array(
				new Statement('@doctrine.dao', array('Venne\Notifications\NotificationEntity')),
				new Statement('@doctrine.dao', array('Venne\Notifications\NotificationUserEntity')),
				new Statement('@doctrine.dao', array('Venne\Notifications\NotificationSettingEntity')),
			));

		$container->addDefinition($this->prefix('emailJob'))
			->setClass('Venne\Notifications\Jobs\EmailJob', array(
				new Statement('@doctrine.dao', array('Venne\Notifications\NotificationUserEntity')),
				new Statement('@doctrine.dao', array('Venne\Security\UserEntity')),
			));
	}


	public function beforeCompile()
	{
		$container = $this->getContainerBuilder();
		$extensions = $this->compiler->getExtensions('Kdyby\Events\DI\EventsExtension');

		$evm = $container->getDefinition(reset($extensions)->prefix('manager'));
		$evm->setClass('Venne\Notifications\EventManager', $evm->factory->arguments);
	}


	/**
	 * @return array
	 */
	public function getJobClasses()
	{
		return array(
			'Venne\Notifications\Jobs\EmailJob',
			'Venne\Notifications\Jobs\NotificationJob',
		);
	}


	/**
	 * @return array
	 */
	public function getPresenterMapping()
	{
		return array(
			'Notifications' => 'Venne\Notifications\*Module\*Presenter',
		);
	}


	/**
	 * @return array
	 */
	public function getEntityMappings()
	{
		return array(
			'Venne\Notifications' => dirname(__DIR__) . '/*Entity.php',
		);
	}

}
