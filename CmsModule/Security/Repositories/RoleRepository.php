<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace CmsModule\Security\Repositories;

use Venne;
use DoctrineModule\Repositories\BaseRepository;
use CmsModule\Entities\UserEntity;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class RoleRepository extends BaseRepository {


	public function save($entity, $withoutFlush = self::FLUSH)
	{
		$en = $entity;
		while(($en = $en->getParent())){
			if($en ==$entity) {
				throw new \Nette\InvalidArgumentException('Cyclic recursion detected. Please set else parent.');
			}
		}

		return parent::save($entity, $withoutFlush);
	}
}
