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

use CmsModule\Content\Entities\BaseFileEntity;
use DoctrineModule\Repositories\BaseRepository;
use Nette\DI\Container;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
abstract class AbstractFileRepository extends BaseRepository
{

	/** @var Container */
	private $container;


	/**
	 * @param Container $container
	 */
	public function injectContainer(Container $container)
	{
		$this->container = $container;
	}


	public function createNew($arguments = array())
	{
		/** @var BaseFileEntity $entity */
		$entity = parent::createNew($arguments);
		$entity->setUser($this->container->getByType('Nette\Security\User'));
		return $entity;
	}
}
