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
class SecurityExtension extends \Nette\DI\CompilerExtension
	implements
	\Kdyby\Doctrine\DI\IEntityProvider,
	\Venne\Notifications\DI\IEventProvider,
	\Venne\System\DI\IPresenterProvider
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
			->setClass('Venne\Security\AdminModule\RoleFormFactory', array(new Statement('@system.admin.ajaxFormFactory')));

		$container->addDefinition($this->prefix('providerFormFactory'))
			->setClass('Venne\Security\AdminModule\ProviderFormFactory', array(new Statement('@system.admin.basicFormFactory')));

		$container->addDefinition($this->prefix('providersFormFactory'))
			->setClass('Venne\Security\AdminModule\ProvidersFormFactory', array(new Statement('@system.admin.ajaxFormFactory')));

		$container->addDefinition($this->prefix('defaultPresenter'))
			->setClass('Venne\Security\AdminModule\DefaultPresenter', array(new Statement('@doctrine.dao', array('Venne\Security\UserEntity'))))
			->addTag(SystemExtension::TAG_ADMINISTRATION, array(
				'link' => 'Security:Admin:Default:',
				'category' => 'System',
				'name' => 'Security',
				'description' => 'Manage users, roles and permissions',
				'priority' => 60,
			));

		$container->addDefinition($this->prefix('rolesTableFactory'))
			->setClass('Venne\Security\AdminModule\RolesTableFactory', array(
				new Statement('@doctrine.dao', array('Venne\Security\RoleEntity'))
			));

		$container->addDefinition($this->prefix('invitationsTableFactory'))
			->setClass('Venne\Security\AdminModule\InvitationsTableFactory', array(
				new Statement('@doctrine.dao', array('Venne\System\InvitationEntity'))
			));

		$container->addDefinition($this->prefix('rolesPresenter'))
			->setClass('Venne\Security\AdminModule\RolesPresenter');

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
	 * @return string[]
	 */
	public function getEntityMappings()
	{
		return array(
			'Venne\Security' => dirname(__DIR__) . '/*Entity.php',
		);
	}

	/**
	 * @return string[]
	 */
	public function getPresenterMapping()
	{
		return array(
			'Security' => 'Venne\Security\*Module\*Presenter',
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

}
