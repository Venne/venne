<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Queue;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 *
 * @property-read integer $id
 * @property-read string $type
 * @property-read mixed[] $arguments
 * @property-read string $state
 * @property-read int $priority
 * @property-read \DateTime $date
 * @property-read \DateTime|null $dateInterval
 * @property-read int|null $round
 *
 * @property-read string $user
 * @property-read string $userEmail
 */
class JobDto extends \Venne\DataTransfer\DataTransferObject
{

	/**
	 * @return string
	 */
	protected function getUser()
	{
		return (string) $this->getRawValue('user');
	}

	/**
	 * @return string
	 */
	protected function getUserEmail()
	{
		return $this->getRawValue('user')->getEmail();
	}

}
