<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\System\Invitation;

use Exception;
use Nette\InvalidStateException;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class InvitationNotFoundException extends InvalidStateException
{

	/** @var int */
	private $invitationId;

	/**
	 * @param int $invitationId
	 * @param \Exception $previous
	 */
	public function __construct($invitationId, Exception $previous = null)
	{
		parent::__construct(sprintf('Invitation #%d does not exist', $invitationId), 0, $previous);

		$this->invitationId = $invitationId;
	}

	/**
	 * @return int
	 */
	public function getInvitationId()
	{
		return $this->invitationId;
	}

}
