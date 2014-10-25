<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\System\Forms\DoctrineForms\Controls;

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\PersistentCollection;
use Kdyby\DoctrineForms\EntityFormMapper;
use Nette\ComponentModel\Component;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Controls\RadioList;
use Nette\Forms\Controls\SelectBox;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class TextControl extends \Nette\Object implements \Kdyby\DoctrineForms\IComponentMapper
{

	/** @var \Kdyby\DoctrineForms\EntityFormMapper */
	private $mapper;

	/** @var \Symfony\Component\PropertyAccess\PropertyAccessor */
	private $accessor;

	/** @var \Doctrine\ORM\EntityManager */
	private $entityManager;

	public function setEntityFormMapper(EntityFormMapper $mapper)
	{
		$this->mapper = $mapper;
		$this->entityManager = $this->mapper->getEntityManager();
		$this->accessor = $mapper->getAccessor();
	}

	/**
	 * @param \Doctrine\ORM\Mapping\ClassMetadata $meta
	 * @param \Nette\ComponentModel\Component $component
	 * @param mixed $entity
	 * @return boolean
	 */
	public function load(ClassMetadata $meta, Component $component, $entity)
	{
		if (!$component instanceof BaseControl) {
			return false;
		}

		if ($meta->hasField($name = $component->getOption(self::FIELD_NAME, $component->getName()))) {
			$component->setValue($this->accessor->getValue($entity, $name));

			return true;
		}

		if (!$meta->hasAssociation($name)) {
			return false;
		}

		/** @var SelectBox|RadioList $component */
		if (($component instanceof SelectBox || $component instanceof RadioList || $component instanceof \Nette\Forms\Controls\MultiChoiceControl) && !count($component->getItems())) {
			if (!$nameKey = $component->getOption(self::ITEMS_TITLE, false)) {
				$path = $component->lookupPath('Nette\Application\UI\Form');
				throw new \Kdyby\DoctrineForms\InvalidStateException(
					'Either specify items for ' . $path . ' yourself, or set the option Kdyby\DoctrineForms\IComponentMapper::ITEMS_TITLE ' .
					'to choose field that will be used as title'
				);
			}

			$criteria = $component->getOption(self::ITEMS_FILTER, array());
			$orderBy = $component->getOption(self::ITEMS_ORDER, array());

			$related = $this->relatedMetadata($entity, $name);
			$items = $this->findPairs($related, $criteria, $orderBy, $nameKey);
			$component->setItems($items);
		}

		if ($meta->isCollectionValuedAssociation($name)) {
			$collection = $meta->getFieldValue($entity, $name);
			if ($collection instanceof PersistentCollection) {
				$values = array();
				foreach ($collection as $value) {
					$values[] = $value->getId();
				}
				$component->setDefaultValue($values);
			}

		} elseif ($relation = $this->accessor->getValue($entity, $name)) {
			$UoW = $this->entityManager->getUnitOfWork();
			$component->setValue($UoW->getSingleIdentifierValue($relation));
		}

		return true;
	}

	/**
	 * @param string|object $entity
	 * @param string $relationName
	 * @return ClassMetadata|Kdyby\Doctrine\Mapping\ClassMetadata
	 */
	private function relatedMetadata($entity, $relationName)
	{
		$meta = $this->entityManager->getClassMetadata(is_object($entity) ? get_class($entity) : $entity);
		$targetClass = $meta->getAssociationTargetClass($relationName);

		return $this->entityManager->getClassMetadata($targetClass);
	}

	/**
	 * @param ClassMetadata $meta
	 * @param array $criteria
	 * @param array $orderBy
	 * @param string|callable $nameKey
	 * @return array
	 */
	private function findPairs(ClassMetadata $meta, $criteria, $orderBy, $nameKey)
	{
		$repository = $this->entityManager->getRepository($meta->getName());

		if ($repository instanceof Kdyby\Doctrine\EntityDao && !is_callable($nameKey)) {
			return $repository->findPairs($criteria, $nameKey, $orderBy);
		}

		$items = array();
		$idKey = $meta->getSingleIdentifierFieldName();
		foreach ($repository->findBy($criteria, $orderBy) as $entity) {
			$items[$this->accessor->getValue($entity, $idKey)] = is_callable($nameKey)
				? \Nette\Utils\Callback::invoke($nameKey, $entity)
				: $this->accessor->getValue($entity, $nameKey);
		}

		return $items;
	}

	/**
	 * @param \Doctrine\ORM\Mapping\ClassMetadata $meta
	 * @param \Nette\ComponentModel\Component $component
	 * @param mixed $entity
	 * @return boolean
	 */
	public function save(ClassMetadata $meta, Component $component, $entity)
	{
		if (!$component instanceof BaseControl) {
			return false;
		}

		if ($meta->hasField($name = $component->getOption(self::FIELD_NAME, $component->getName()))) {
			$this->accessor->setValue($entity, $name, $component->getValue());

			return true;
		}

		if (!$meta->hasAssociation($name)) {
			return false;
		}

		$identifier = $component->getValue();
		if (!$identifier && !is_array($identifier)) {
			return false;
		}

		$entityClass = $this->relatedMetadata($entity, $name)->getName();
		$repository = $this->entityManager->getRepository($entityClass);

		if ($meta->isCollectionValuedAssociation($name)) {
			$property = \Doctrine\Common\Util\Inflector::singularize($name);
			foreach ($repository->findAll() as $associatedEntity) {
				if (in_array($associatedEntity->id, $identifier)) {
					$hasMethod = 'has' . ucfirst($property);
					if (!$entity->$hasMethod($associatedEntity)) {
						$addMethod = 'add' . ucfirst($property);
						$entity->$addMethod($associatedEntity);
					}

				} else {
					$removeMethod = 'remove' . ucfirst($property);
					$entity->$removeMethod($associatedEntity);
				}
			}

		} elseif ($relation = $repository->find($identifier)) {
			$meta->setFieldValue($entity, $name, $relation);
		}

		return true;
	}

}
