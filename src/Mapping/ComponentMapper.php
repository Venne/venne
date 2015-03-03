<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Mapping;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
interface ComponentMapper
{

	/**
	 * @param mixed $entity
	 * @return mixed
	 * @throws \Venne\Mapping\InvalidArgument
	 */
	public function load($entity);

	/**
	 * @param mixed $entity
	 * @param mixed[] $value
	 * @throws \Venne\Mapping\InvalidArgument
	 */
	public function save($entity, array $value);

}
