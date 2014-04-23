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
abstract class Event implements IEvent
{

	/**
	 * @return string
	 */
	public static function getName()
	{
		return get_called_class();
	}


	/**
	 * @param NotificationEntity $log
	 * @return null
	 */
	public static function getLink(NotificationEntity $log)
	{
		return NULL;
	}

}
