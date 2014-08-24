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
	 * @return \Venne\Security\LoginProviderEntity
	 */
	public function getLoginProviderEntity();

	/**
	 * @param \Venne\Security\UserEntity $userEntity
	 */
	public function connectWithUser(UserEntity $userEntity);

	/**
	 * @param mixed[] $parameters
	 */
	public function setAuthenticationParameters(array $parameters);

	/**
	 * @return \Nette\Forms\Container|null
	 */
	public function getFormContainer();

}
