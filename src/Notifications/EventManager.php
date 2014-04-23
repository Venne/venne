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

use Doctrine\Common\EventArgs;
use Kdyby\Events\LazyEventManager;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class EventManager extends LazyEventManager
{

	/**
	 * @param string $eventName
	 * @param EventArgs $eventArgs
	 * @return bool|void
	 */
	public function dispatchEvent($eventName, EventArgs $eventArgs = NULL)
	{
		parent::dispatchEvent($eventName, $eventArgs);
	}

}

