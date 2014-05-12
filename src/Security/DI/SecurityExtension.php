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

use Kdyby\Doctrine\DI\IEntityProvider;
use Kdyby\Events\DI\EventsExtension;
use Nette\DI\CompilerExtension;
use Nette\DI\ContainerBuilder;
use Nette\DI\Statement;
use Venne\Notifications\DI\IEventProvider;
use Venne\System\DI\IPresenterProvider;
use Venne\System\DI\SystemExtension;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class SecurityExtension extends CompilerExtension implements IEntityProvider, IEventProvider, IPresenterProvider
{

	public function loadConfiguration()
	{
		$container = $this->getContainerBuilder();

		$container->addDefinition($this->prefix('listeners.userStateListener'))
			->setClass('Venne\Security\Listeners\UserStateListener');

		$container->addDefinition($this->prefix('resetFormFactory'))
			->setClass('Venne\Security\Login\ResetFormFactory', array(new Statement('@system.admin.basicFormFactory')));

		$container->addDefinition($this->prefix('confirmFormFactory'))
			->setClass('Venne\Security\Login\ConfirmFormFactory', array(new Statement('@system.admin.basicFormFactory')));

		$container->addDefinition($this->prefix('extendedUserListener'))
			->setClass('Venne\Security\Listeners\ExtendedUserListener');

		$container->addDefinition($this->prefix('userLogListener'))
			->setClass('Venne\Security\Listeners\UserLogListener')
			->addTag(EventsExtension::TAG_SUBSCRIBER);


		$this->setupDefaultType($container);
		$this->setupSecurity($container);
	}


	public function setupSecurity(ContainerBuilder $container)
	{
		$container->addDefinition($this->prefix('permissionsFormFactory'))
			->setClass('Venne\Security\AdminModule\PermissionsFormFactory', array(
				new Statement('@system.admin.basicFormFactory'),
				new Statement('@doctrine.dao', array('Venne\Security\RoleEntity'))
			));

		$container->addDefinition($this->prefix('roleFormFactory'))
			->setClass('Venne\Security\AdminModule\RoleFormFactory', array(new Statement('@system.admin.basicFormFactory')));

		$container->addDefinition($this->prefix('providerFormFactory'))
			->setClass('Venne\Security\AdminModule\ProviderFormFactory', array(new Statement('@system.admin.basicFormFactory')));

		$container->addDefinition($this->prefix('providersFormFactory'))
			->setClass('Venne\Security\AdminModule\ProvidersFormFactory', array(new Statement('@system.admin.basicFormFactory')));


		$container->addDefinition($this->prefix('defaultPresenter'))
			->setClass('Venne\Security\AdminModule\DefaultPresenter', array(new Statement('@doctrine.dao', array('Venne\Security\UserEntity'))))
			->addTag(SystemExtension::TAG_ADMINISTRATION, array(
				'link' => 'Security:Admin:Default:',
				'category' => 'System',
				'name' => 'Security',
				'description' => 'Manage users, roles and permissions',
				'priority' => 60,
			));

		$container->addDefinition($this->prefix('rolesPresenter'))
			->setClass('Venne\Security\AdminModule\RolesPresenter', array(new Statement('@doctrine.dao', array('Venne\Security\RoleEntity'))));

		$container->addDefinition($this->prefix('accountPresenter'))
			->setClass('Venne\Security\AdminModule\AccountPresenter', array(
				new Statement('@doctrine.dao', array('Venne\Security\UserEntity')),
				new Statement('@doctrine.dao', array('Venne\Security\LoginEntity'))
			));
	}


	public function setupDefaultType(ContainerBuilder $container)
	{
		$container->addDefinition($this->prefix('userType'))
			->setClass('Venne\Security\UserType', array('Venne\Security\DefaultType\UserEntity'))
			->addSetup('setFormFactory', array(new Statement('@Venne\Security\DefaultType\AdminFormFactory')))
			->addSetup('setFrontFormFactory', array(new Statement('@Venne\Security\DefaultType\FrontFormFactory')))
			->addSetup('setRegistrationFormFactory', array(new Statement('@Venne\Security\DefaultType\RegistrationFormFactory')))
			->addTag(SystemExtension::TAG_USER, array(
				'name' => 'Default user',
			));

		$container->addDefinition($this->prefix('adminFormFactory'))
			->setClass('Venne\Security\DefaultType\AdminFormFactory', array(new Statement('@system.admin.basicFormFactory')));

		$container->addDefinition($this->prefix('frontFormFactory'))
			->setClass('Venne\Security\DefaultType\FrontFormFactory', array(new Statement('@system.admin.basicFormFactory')));

		$container->addDefinition($this->prefix('registrationFormFactory'))
			->setClass('Venne\Security\DefaultType\RegistrationFormFactory', array(new Statement('@system.admin.basicFormFactory')));
	}


	/**
	 * @return array
	 */
	public function getEntityMappings()
	{
		return array(
			'Venne\Security' => dirname(__DIR__) . '/*Entity.php',
		);
	}


	/**
	 * @return array
	 */
	public function getPresenterMapping()
	{
		return array(
			'Security' => 'Venne\Security\*Module\*Presenter',
		);
	}


	/**
	 * @return array
	 */
	public function getEventTypes()
	{
		return array(
			'Venne\Security\Events\LoginEvent',
			'Venne\Security\Events\RegistrationEvent'
		);
	}

}
