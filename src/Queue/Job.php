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

use Nette\Object;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
abstract class Job implements IJob
{

	public static function getName()
	{
		return get_called_class();
	}

}
