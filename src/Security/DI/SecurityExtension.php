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
use Nette\Security\Permission;
use Venne\Security\AdminModule\AccountPresenter;
use Venne\Security\AdminModule\DefaultPresenter;
use Venne\Security\Authenticator;
use Venne\Security\AuthorizatorFactory;
use Venne\Security\User\DefaultType\Mapping\PasswordContainer;
use Venne\Security\Events\LoginEvent;
use Venne\Security\Events\NewPasswordEvent;
use Venne\Security\Events\PasswordRecoveryEvent;
use Venne\Security\Events\RegistrationEvent;
use Venne\Security\User\ExtendedUserListener;
use Venne\Security\User\UserLogListener;
use Venne\Security\User\UserStateListener;
use Venne\Security\SecurityManager;
use Venne\Security\User\UserStorage;
use Venne\System\Commands\InstallCommand;
use Venne\System\DI\SystemExtension;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class SecurityExtension extends \Nette\DI\CompilerExtension implements
	\Kdyby\Doctrine\DI\IEntityProvider,
	\Venne\Notifications\DI\IEventProvider,
	\Venne\System\DI\IPresenterProvider,
	\Venne\System\DI\IFormMapperProvider,
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
			->setClass(UserStateListener::class);

		$container->addDefinition($this->prefix('extendedUserListener'))
			->setClass(ExtendedUserListener::class);

		$container->addDefinition($this->prefix('userLogListener'))
			->setClass(UserLogListener::class)
			->addTag(EventsExtension::TAG_SUBSCRIBER);

		$container->addDefinition($this->prefix('securityManager'))
			->setClass(SecurityManager::class);

		$container->addDefinition($this->prefix('authorizatorFactory'))
			->setFactory(AuthorizatorFactory::class);

		$container->getDefinition('packageManager.packageManager')
			->addSetup('$service->onInstall[] = ?->clearPermissionSession', array($this->prefix('@authorizatorFactory')))
			->addSetup('$service->onUninstall[] = ?->clearPermissionSession', array($this->prefix('@authorizatorFactory')));

		$container->getDefinition('nette.userStorage')
			->setClass(UserStorage::class);

		$container->addDefinition('authorizator')
			->setClass(Permission::class)
			->setFactory($this->prefix('@authorizatorFactory') . '::getPermissionsByUser', array('@user', true));

		$container->addDefinition('authenticator')
			->setClass(Authenticator::class);

		$container->addDefinition($this->prefix('installCommand'))
			->setFactory(InstallCommand::class)
			->addTag(ConsoleExtension::COMMAND_TAG);

		$container->addDefinition($this->prefix('passwordContainerMapper'))
			->setClass(PasswordContainer::class);

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
			->setClass(DefaultPresenter::class)
			->addTag(SystemExtension::TAG_ADMINISTRATION, array(
				'link' => 'Admin:Security:Default:',
				'category' => 'System',
				'name' => 'Security',
				'description' => 'Manage users, roles and permissions',
				'priority' => 60,
			));

		$container->addDefinition($this->prefix('accountPresenter'))
			->setClass(AccountPresenter::class);
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
			$class = '\\' . $container->getDefinition($item)->getClass();
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
				\Venne\Security\User\DefaultType\User::class,
				new Statement('@' . \Venne\Security\User\DefaultType\AdminFormService::class),
				new Statement('@' . \Venne\Security\User\DefaultType\FrontFormService::class),
				new Statement('@' . \Venne\Security\User\DefaultType\RegistrationFormService::class)
			),
		);
	}

	/**
	 * @return string[]
	 */
	public function getFormMappers()
	{
		return array(
			PasswordContainer::class,
		);
	}

}
