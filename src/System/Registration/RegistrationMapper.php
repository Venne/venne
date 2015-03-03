<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\System\Registration;

use Doctrine\ORM\EntityManager;
use Venne\Mapping\InvalidArgument;
use Venne\Security\Role\Role;
use Venne\Mapping\ComponentMapper;
use Venne\Utils\Object;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class RegistrationMapper extends Object implements ComponentMapper
{

	/** @var \Kdyby\Doctrine\EntityRepository */
	private $roleRepository;

	public function __construct(EntityManager $entityManager)
	{
		$this->roleRepository = $entityManager->getRepository(Role::class);
	}

	/**
	 * @param \Venne\System\Registration\Registration $entity
	 * @return mixed[]
	 */
	public function load($entity)
	{
		if (!$entity instanceof Registration) {
			throw new InvalidArgument();
		}

		return array(
			'enabled' => $entity->isEnabled(),
			'name' => $entity->getName(),
			'mode' => $entity->getMode()->getValue(),
			'loginProviderMode' => $entity->getLoginProviderMode()->getValue(),
			'roles' => array_map(function (Role $role) {
				return $role->getId();
			}, $entity->getRoles()),
		);
	}

	/**
	 * @param \Venne\System\Registration\Registration $entity
	 * @param mixed[] $values
	 */
	public function save($entity, array $values)
	{
		if (!$entity instanceof Registration) {
			throw new InvalidArgument();
		}

		if (isset($values['enabled'])) {
			$entity->setEnabled($values['enabled']);
		}

		if (isset($values['name'])) {
			$entity->setName($values['name']);
		}

		if (isset($values['mode'])) {
			$entity->setMode(RegistrationMode::get($values['mode']));
		}

		if (isset($values['loginProviderMode'])) {
			$entity->setLoginProviderMode(LoginProviderMode::get($values['loginProviderMode']));
		}

		if (isset($values['roles'])) {
			foreach ($entity->getRoles() as $role) {
				$entity->removeRole($role);
			}

			foreach ($values['roles'] as $role) {
				$entity->addRole($this->roleRepository->find($role));
			}
		}
	}

}
