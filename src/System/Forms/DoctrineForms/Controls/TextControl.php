<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.md that was distributed with this source code.
 */

namespace Venne\System\Forms\DoctrineForms\Controls;

use Doctrine\ORM\Mapping\ClassMetadata;
use Kdyby\DoctrineForms\EntityFormMapper;
use Nette\ComponentModel\Component;
use Nette\Forms\Controls\BaseControl;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class TextControl extends \Kdyby\DoctrineForms\Controls\TextControl
{

	/** @var \Kdyby\DoctrineForms\EntityFormMapper */
	private $mapper;

	/** @var \Symfony\Component\PropertyAccess\PropertyAccessor */
	private $accessor;

	/** @var \Doctrine\ORM\EntityManager */
	private $entityManager;

	public function __construct()
	{
	}

	public function setEntityFormMapper(EntityFormMapper $mapper)
	{
		parent::__construct($mapper);
		$this->mapper = $mapper;
		$this->entityManager = $this->mapper->getEntityManager();
		$this->accessor = $mapper->getAccessor();
	}

	/**
	 * @param \Doctrine\ORM\Mapping\ClassMetadata $meta
	 * @param \Nette\ComponentModel\Component $component
	 * @param object $entity
	 * @return boolean
	 */
	public function load(ClassMetadata $meta, Component $component, $entity)
	{
		if (parent::load($meta, $component, $entity)) {
			return true;
		}

		if (!$component instanceof BaseControl) {
			return false;
		}

		$name = $component->getOption(self::FIELD_NAME, $component->getName());

		try {
			$value = $this->accessor->getValue($entity, $name);
			$component->setValue($value);
		} catch (\Exception $e) {

		}

		return true;
	}

	/**
	 * @param \Doctrine\ORM\Mapping\ClassMetadata $meta
	 * @param \Nette\ComponentModel\Component $component
	 * @param object $entity
	 * @return boolean
	 */
	public function save(ClassMetadata $meta, Component $component, $entity)
	{
		if (parent::save($meta, $component, $entity)) {
			return true;
		}

		if (!$component instanceof BaseControl) {
			return false;
		}

		$name = $component->getOption(self::FIELD_NAME, $component->getName());

		try {
			$this->accessor->setValue($entity, $name, $component->getValue());
		} catch (\Exception $e) {

		}

		return true;
	}

}
