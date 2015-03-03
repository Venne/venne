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
class LoginProviderMode extends Enum
{

	const LOAD = 'load';
	const LOAD_AND_SAVE = 'load&save';

	public static function getLabels()
	{
		return array(
			self::LOAD => 'only load user data',
			self::LOAD_AND_SAVE => 'load user data and save',
		);
	}

}
