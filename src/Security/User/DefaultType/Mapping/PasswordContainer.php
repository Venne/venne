<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Security\User\DefaultType\Mapping;

use Doctrine\ORM\Mapping\ClassMetadata;
use Kdyby\DoctrineForms\EntityFormMapper;
use Nette\ComponentModel\Component;
use Venne\Security\User\User;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class PasswordContainer extends \Nette\Object implements \Kdyby\DoctrineForms\IComponentMapper
{

	/** @var \Kdyby\DoctrineForms\EntityFormMapper */
	private $mapper;

	public function setEntityFormMapper(EntityFormMapper $mapper)
	{
		$this->mapper = $mapper;
	}

	/**
	 * @param ClassMetadata $meta
	 * @param Component $component
	 * @param object $entity
	 * @return bool
	 */
	public function load(ClassMetadata $meta, Component $component, $entity)
	{
		if (!$component instanceof \Venne\Security\User\DefaultType\PasswordContainer) {
			return false;
		}

		if (!$entity instanceof User) {
			return false;
		}

		return true;
	}

	/**
	 * @param ClassMetadata $meta
	 * @param Component $component
	 * @param object $entity
	 * @return bool
	 */
	public function save(ClassMetadata $meta, Component $component, $entity)
	{
		if (!$component instanceof \Venne\Security\User\DefaultType\PasswordContainer) {
			return false;
		}

		if (!$entity instanceof User) {
			return false;
		}

		if ($component->isPasswordSet()) {
			$entity->setPassword($component->getValue());
		}

		return true;
	}

}
