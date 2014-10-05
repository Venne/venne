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

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class EventManager extends \Kdyby\Events\LazyEventManager
{

	/**
	 * @param string $eventName
	 * @param \Doctrine\Common\EventArgs $eventArgs
	 * @return bool|null
	 */
	public function dispatchEvent($eventName, EventArgs $eventArgs = null)
	{
		parent::dispatchEvent($eventName, $eventArgs);
	}

}
