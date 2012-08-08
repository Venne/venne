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
use DoctrineModule\ORM\BaseRepository;
use CmsModule\Entities\UserEntity;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class RoleRepository extends BaseRepository {


	/**
	 * Save structure
	 *
	 * @param array $data
	 */
	public function setStructure($data, $withoutFlush = self::FLUSH)
	{
		foreach ($data as $item) {
			foreach ($item as $item2) {
				$entity = $this->find($item2["id"]);
				$entity->parent = $item2["role_id"] ? $this->find($item2["role_id"]) : NULL;
			}
		}

		$this->flush($withoutFlush);
	}

}
