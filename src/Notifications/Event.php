<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Notifications;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
abstract class Event implements \Venne\Notifications\IEvent
{

	/**
	 * @return string
	 */
	public static function getName()
	{
		return get_called_class();
	}

	/**
	 * @param \Venne\Notifications\Notification $log
	 * @return null
	 */
	public static function getLink(Notification $log)
	{
		return null;
	}

}
