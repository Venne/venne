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

use Venne\Utils\Type\Enum;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class RegistrationMode extends Enum
{

	const BASIC = 'basic';
	const CHECKUP = 'checkup';
	const MAIL = 'mail';
	const MAIL_CHECKUP = 'mail&checkup';

	/**
	 * @return string[]
	 */
	public static function getLabels()
	{
		return array(
			self::BASIC => 'basic registration',
			self::CHECKUP => 'registration with admin confirmation',
			self::MAIL => 'registration with e-mail confirmation',
			self::MAIL_CHECKUP => 'registration with e-mail and admin confirmation'
		);
	}

}
