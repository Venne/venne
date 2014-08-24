<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\System\Events;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class InvitationEvent extends \Venne\Notifications\Event
{

	/**
	 * @return string
	 */
	public static function getHumanName()
	{
		return 'User invitation';
	}

}
