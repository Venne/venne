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

use DoctrineModule\Repositories\BaseRepository;
use Nette;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class UserRepository extends BaseRepository
{

	public function createNew($arguments = array())
	{
		if (!count($arguments)) {
			$userPage = $this->getEntityManager()
				->getRepository('CmsModule\Content\Entities\PageEntity')
				->findOneBy(array('special' => 'users'));

			$arguments = array($this->getEntityManager()
				->getRepository($userPage->class)
				->findOneBy(array('page' => $userPage->id)));
		}
		return parent::createNew($arguments);
	}

}
