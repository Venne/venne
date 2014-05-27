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

use Nette\Security\Permission;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class Authorizator extends Permission
{

	public function __construct()
	{
		$this->addRole('guest');
		$this->addRole('admin', 'guest');
	}


	public function isAllowed($role = self::ALL, $resource = self::ALL, $privilege = self::ALL)
	{
		if ($resource !== self::ALL && !$this->hasResource($resource)) {
			$this->addResource($resource);
		}

		return parent::isAllowed($role, $resource, $privilege);
	}

}
