<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace CmsModule\Content;

use CmsModule\Content\Entities\VersionableTrait;
use CmsModule\Content\Entities\VersionEntity;
use Doctrine\ORM\EntityManager;
use Nette\InvalidArgumentException;
use Nette\Object;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class VersionManager extends Object
{

	/** @var EntityManager */
	private $entityManager;


	/**
	 * @param EntityManager $entityManager
	 */
	public function __construct(EntityManager $entityManager)
	{
		$this->entityManager = $entityManager;
	}


	/**
	 * @param VersionableTrait $entity
	 * @param $version
	 * @throws \Nette\InvalidArgumentException
	 */
	public function checkoutVersion(VersionableTrait $entity, $version)
	{
		if (!isset($entity->versions[$version])) {
			throw new InvalidArgumentException("Version $version does not exist.");
		}

		$this->setDataToEntity($entity, $version->getData());
		$entity->setCurrentVersion($version);
	}


	/**
	 * @param VersionableTrait $entity
	 * @return VersionEntity
	 */
	public function createVersion(VersionableTrait $entity)
	{
		$version = count($entity->getVersions());
		$entity->versions[$version] = $version = new VersionEntity($version, $this->getDataFromEntity($entity));
		$entity->currentVersion = NULL;
		return $version;
	}


	private function getDataFromEntity(VersionableTrait $entity)
	{
		$metadata = $this->getMetadata($entity);

		foreach ($metadata->columnNames as $column) {
			//$metadata->
		}
	}


	private function setDataToEntity(VersionableTrait $entity)
	{
	}


	/**
	 * @param VersionableTrait $entity
	 * @return \Doctrine\ORM\Mapping\ClassMetadata
	 */
	private function getMetadata(VersionableTrait $entity)
	{
		return $this->entityManager->getClassMetadata(get_class($entity));
	}
}

