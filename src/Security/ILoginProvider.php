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
use Venne\Security\User\User;

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
	 * @return \Venne\Security\LoginProvider
	 */
	public function getLoginProviderEntity();

	/**
	 * @param \Venne\Security\User\User $user
	 */
	public function connectWithUser(User $user);

	/**
	 * @param mixed[] $parameters
	 */
	public function setAuthenticationParameters(array $parameters);

	/**
	 * @return \Nette\Forms\Container|null
	 */
	public function getFormContainer();

}
