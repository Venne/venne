<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\System\Registration;

use Exception;
use Nette\InvalidStateException;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class RegistrationNotFoundException extends InvalidStateException
{

	/** @var int */
	private $registrationId;

	/**
	 * @param int $registrationId
	 * @param \Exception $previous
	 */
	public function __construct($registrationId, Exception $previous = null)
	{
		parent::__construct(sprintf('Registration #%d does not exist', $registrationId), 0, $previous);

		$this->registrationId = $registrationId;
	}

	/**
	 * @return int
	 */
	public function getRegistrationId()
	{
		return $this->registrationId;
	}

}
