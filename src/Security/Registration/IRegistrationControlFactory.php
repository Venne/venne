<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Security\Registration;

use Venne\System\Registration\RegistrationMode;
use Venne\System\Registration\LoginProviderMode;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
interface IRegistrationControlFactory
{

	/**
	 * @param string $userType
	 * @param \Venne\System\Registration\RegistrationMode $mode
	 * @param \Venne\System\Registration\LoginProviderMode $loginProviderMode
	 * @param \Venne\Security\Role\Role[] $roles
	 * @return \Venne\Security\Registration\RegistrationControl
	 */
	public function create($userType, RegistrationMode $mode, LoginProviderMode $loginProviderMode, $roles);

}
