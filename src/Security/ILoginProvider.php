<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Security;

use Venne\System\Pages\Users\UserEntity;
use Venne\Security\Entities\LoginProviderEntity;
use Nette\Forms\Container;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
interface ILoginProvider extends \Nette\Security\IAuthenticator
{

	/**
	 * @return string
	 */
	public static function getType();

	/**
	 * @return LoginProviderEntity
	 */
	public function getLoginProviderEntity();


	/**
	 * @param UserEntity $userEntity
	 */
	public function connectWithUser(UserEntity $userEntity);


	/**
	 * @param array $parameters
	 */
	public function setAuthenticationParameters(array $parameters);


	/**
	 * @return Container|NULL
	 */
	public function getFormContainer();

}
