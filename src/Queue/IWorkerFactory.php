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
 */
interface IWorkerFactory
{

	/**
	 * @param int $id
	 * @param int $interval
	 * @return \Venne\Queue\Worker
	 */
	public function create($id, $interval);

}
