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
 *
 * @property-read integer $id
 * @property-read \DateTime $created
 * @property-read string $target
 * @property-read integer $targetKey
 * @property-read boolean $markRead
 *
 * @property-read string $userEmail
 * @property-read string $userName
 *
 * @property-read string $action
 * @property-read string $message
 */
class NotificationDto extends \Venne\DataTransfer\DataTransferObject
{

	/**
	 * @return \DateTime
	 */
	protected function getCreated()
	{
		return $this->getNotification()->created;
	}

	/**
	 * @return string
	 */
	protected function getTarget()
	{
		return $this->getNotification()->target;
	}

	/**
	 * @return integer
	 */
	protected function getTargetKey()
	{
		return $this->getNotification()->targetKey;
	}

	/**
	 * @return string
	 */
	protected function getUserName()
	{
		return (string) $this->getNotification()->user;
	}

	/**
	 * @return string
	 */
	protected function getUserEmail()
	{
		return (string) $this->getNotification()->user->email;
	}

	/**
	 * @return string
	 */
	protected function getAction()
	{
		return $this->getNotification()->type->action;
	}

	/**
	 * @return string
	 */
	protected function getMessage()
	{
		return $this->getNotification()->type->message;
	}

	/**
	 * @return Notification
	 */
	private function getNotification()
	{
		return $this->getRawValue('notification');
	}

}
