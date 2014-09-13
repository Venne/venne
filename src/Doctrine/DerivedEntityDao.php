<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Doctrine;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class DerivedEntityDao extends \Kdyby\Doctrine\EntityDao
{

	public function save($entity = null, $relations = null)
	{
		$metadata = $this->getEntityManager()->getClassMetadata(get_class($entity));
		$primaryProperty = $metadata->getSingleIdReflectionProperty()->getName();

		$targetEntity = $entity->$primaryProperty;

		$this->getEntityManager()
			->getRepository($targetEntity::getClassName())
			->save($targetEntity);

		return parent::save($entity, $relations);
	}

}
