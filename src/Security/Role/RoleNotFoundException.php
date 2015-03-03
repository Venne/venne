<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Security\Role;

use Exception;
use Nette\InvalidStateException;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class RoleNotFoundException extends InvalidStateException
{

	/** @var int */
	private $roleId;

	/**
	 * @param int $roleId
	 * @param \Exception $previous
	 */
	public function __construct($roleId, Exception $previous = null)
	{
		parent::__construct(sprintf('Role #%d does not exist', $roleId), 0, $previous);

		$this->roleId = $roleId;
	}

	/**
	 * @return int
	 */
	public function getRoleId()
	{
		return $this->roleId;
	}

}
