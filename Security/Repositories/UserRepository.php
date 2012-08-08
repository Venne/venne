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
class UserRepository extends BaseRepository {


	/**
	 * Check if user is unique.
	 *
	 * @param UserEntity $entity
	 * @return boolean
	 * @throws UserNameExistsException
	 * @throws UserEmailExistsException
	 */
	public function isUserUnique(UserEntity $entity)
	{
		$item = $this->repository->findOneByName($entity->name);
		if ($item) {
			throw new UserNameExistsException("Username " . $entity->name . " already exists");
		}
		$item = $this->repository->findOneByEmail($entity->email);
		if ($item) {
			throw new UserEmailExistsException("E-mail " . $entity->email . " already exists");
		}
		return true;
	}

}
