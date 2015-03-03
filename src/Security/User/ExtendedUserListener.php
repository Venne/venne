<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Security\User;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Mapping as ORM;
use Venne\Security\User\User;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class ExtendedUserListener
{

	/**
	 * @ORM\PostLoad
	 *
	 * @param \Venne\Security\User\User $user
	 * @param \Doctrine\ORM\Event\LifecycleEventArgs $event
	 */
	public function postLoadHandler(User $user, LifecycleEventArgs $event)
	{
		$em = $event->getEntityManager();
		$user->setExtendedUserCallback(function () use ($em, $user) {
			return $em->getRepository($user->getClass())->findOneBy(array('user' => $user->id));
		});
	}

}
