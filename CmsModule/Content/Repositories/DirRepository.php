<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace CmsModule\Content\Repositories;

use CmsModule\Content\Entities\PageEntity;
use DoctrineModule\Repositories\BaseRepository;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class DirRepository extends BaseRepository
{

	public function save($entity, $withoutFlush = self::FLUSH)
	{
		$parent = $entity;
		while (($parent = $parent->parent) !== NULL) {
			if ($entity->id === $parent->id) {
				throw new \Nette\InvalidArgumentException('Cyclic association detected!');
			}
		}

		return parent::save($entity, $withoutFlush);
	}
}
