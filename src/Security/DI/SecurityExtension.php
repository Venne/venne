<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Security\DI;

use Kdyby\Console\DI\ConsoleExtension;
use Kdyby\Events\DI\EventsExtension;
use Nette\DI\ContainerBuilder;
use Nette\DI\Statement;
use Venne\Security\Events\LoginEvent;
use Venne\Security\Events\NewPasswordEvent;
use Venne\Security\Events\PasswordRecoveryEvent;
use Venne\Security\Events\RegistrationEvent;
use Venne\System\DI\SystemExtension;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class SecurityExtension extends \Nette\DI\CompilerExtension implements
	\Kdyby\Doctrine\DI\IEntityProvider,
	\Venne\Notifications\DI\IEventProvider,
	\Venne\System\DI\IPresenterProvider,
	\Venne\Security\DI\UserTypeProvider
{

	const TAG_LOGIN_PROVIDER = 'venne.loginProvider';

	public function loadConfiguration()
	{
		$container = $this->getContainerBuilder();
		$this->compiler->parseServices(
			$container,
			$this->loadFromFile(__DIR__ . '/services.neon')
		);

		$container->addDefinition($this->prefix('listeners.userStateListener'))
			->setClass('Venne\Security\Listeners\UserStateListener');

		$container->addDefinition($this->prefix('extendedUserListener'))
			->setClass('Venne\Security\Listeners\ExtendedUserListener');

		$container->addDefinition($this->prefix('userLogListener'))
			->setClass('Venne\Security\Listeners\UserLogListener')
			->addTag(EventsExtension::TAG_SUBSCRIBER);

		$container->addDefinition($this->prefix('securityManager'))
			->setClass('Venne\Security\SecurityManager');

		$container->addDefinition($this->prefix('authorizatorFactory'))
			->setFactory('Venne\Security\AuthorizatorFactory');

		$container->getDefinition('packageManager.packageManager')
			->addSetup('$service->onInstall[] = ?->clearPermissionSession', array($this->prefix('@authorizatorFactory')))
			->addSetup('$service->onUninstall[] = ?->clearPermissionSession', array($this->prefix('@authorizatorFactory')));

		$container->addDefinition('authorizator')
			->setClass('Nette\Security\Permission')
			->setFactory($this->prefix('@authorizatorFactory') . '::getPermissionsByUser', array('@user', true));

		$container->addDefinition('authenticator')
			->setClass('Venne\Security\Authenticator');

		$container->addDefinition($this->prefix('installCommand'))
			->setFactory('Venne\System\Commands\InstallCommand')
			->addTag(ConsoleExtension::COMMAND_TAG);

		$this->setupSecurity($container);
		$this->registerUsers();
	}

	public function beforeCompile()
	{
		$this->registerLoginProvider();
	}

	public function setupSecurity(ContainerBuilder $container)
	{
		$container->addDefinition($this->prefix('defaultPresenter'))
			->setClass('Venne\Security\AdminModule\DefaultPresenter')
			->addTag(SystemExtension::TAG_ADMINISTRATION, array(
				'link' => 'Admin:Security:Default:',
				'category' => 'System',
				'name' => 'Security',
				'description' => 'Manage users, roles and permissions',
				'priority' => 60,
			));

		$container->addDefinition($this->prefix('accountPresenter'))
			->setClass('Venne\Security\AdminModule\AccountPresenter');
	}

	private function registerUsers()
	{
		$container = $this->getContainerBuilder();
		$config = $container->getDefinition($this->prefix('securityManager'));

		foreach ($this->compiler->extensions as $extension) {
			if ($extension instanceof UserTypeProvider) {
				foreach ($extension->getUserTypes() as $type) {
					$config->addSetup(
						'$service->addUserType(new Venne\Security\UserType(?, ?, ?, ?, ?));',
						$type->getArguments()
					);
				}
			}
		}
	}

	private function registerLoginProvider()
	{
		$container = $this->getContainerBuilder();
		$config = $container->getDefinition($this->prefix('securityManager'));

		foreach ($container->findByTag(static::TAG_LOGIN_PROVIDER) as $item => $tags) {
			$class = '\\' . $container->getDefinition($item)->class;
			$type = $class::getType();

			$config->addSetup('addLoginProvider', array($type, (string) $item));
		}
	}

	/**
	 * @return string[]
	 */
	public function getEntityMappings()
	{
		return array(
			'Venne\Security' => dirname(__DIR__) . '/*.php',
		);
	}

	/**
	 * @return string[]
	 */
	public function getPresenterMapping()
	{
		return array(
			'Admin:Security' => 'Venne\*\AdminModule\*Presenter',
		);
	}

	/**
	 * @return string[]
	 */
	public function getEventTypes()
	{
		return array(
			LoginEvent::getName(),
			RegistrationEvent::getName(),
			NewPasswordEvent::getName(),
			PasswordRecoveryEvent::getName(),
		);
	}

	/**
	 * @return \Venne\Security\UserType[]
	 */
	public function getUserTypes()
	{
		return array(
			new UserType(
				'Default user',
				\Venne\Security\DefaultType\User::class,
				new Statement('@' . \Venne\Security\DefaultType\AdminFormService::getReflection()->getName()),
				new Statement('@' . \Venne\Security\DefaultType\FrontFormService::getReflection()->getName()),
				new Statement('@' . \Venne\Security\DefaultType\RegistrationFormService::getReflection()->getName())
			),
		);
	}

}
