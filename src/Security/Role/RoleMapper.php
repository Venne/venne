<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Security\Role;

use Doctrine\ORM\EntityManager;
use Venne\Mapping\InvalidArgument;
use Venne\Mapping\ComponentMapper;
use Venne\Utils\Object;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class RoleMapper extends Object implements ComponentMapper
{

	/** @var \Kdyby\Doctrine\EntityRepository */
	private $roleRepository;

	public function __construct(EntityManager $entityManager)
	{
		$this->roleRepository = $entityManager->getRepository(Role::class);
	}

	/**
	 * @param \Venne\Security\Role\Role $entity
	 * @return mixed[]
	 */
	public function load($entity)
	{
		if (!$entity instanceof Role) {
			throw new InvalidArgument();
		}

		return array(
			'id' => $entity->getId(),
			'name' => $entity->getName(),
			'parent' => $entity->getParent() !== null ? $entity->getParent()->getId() : null,
			'children' => array_map(function (Role $role) {
				return $role->getId();
			}, $entity->getChildren()),
		);
	}

	/**
	 * @param \Venne\Security\Role\Role $entity
	 * @param mixed[] $values
	 */
	public function save($entity, array $values)
	{
		if (!$entity instanceof Role) {
			throw new InvalidArgument();
		}

		if (array_key_exists('name', $values)) {
			$entity->setName($values['name']);
		}

		if (array_key_exists('parent', $values)) {
			$entity->setParent($values['parent'] !== null ? $this->roleRepository->find($values['parent']) : null);
		}

		if (array_key_exists('children', $values)) {
			foreach ($entity->getChildren() as $child) {
				$entity->removeChildren($child);
			}

			foreach ($values['children'] as $id) {
				$entity->addChildren($this->roleRepository->find($id));
			}
		}
	}

}
