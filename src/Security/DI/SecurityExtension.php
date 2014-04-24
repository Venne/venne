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
use Nette\Application\Routers\Route;
use Nette\DI\CompilerExtension;
use Nette\DI\Statement;
use Venne\Notifications\DI\IEventProvider;
use Venne\System\DI\IPresenterProvider;

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
