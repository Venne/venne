<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\System\Registration\Form;

use Doctrine\ORM\EntityManager;
use Nette\Forms\Container;
use Venne\Security\Role\Role;
use Venne\System\Registration\LoginProviderMode;
use Venne\System\Registration\RegistrationMode;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class RegistrationContainer extends Container
{

	/** @var \Kdyby\Doctrine\EntityRepository */
	private $roleRepository;

	public function __construct(
		EntityManager $entityManager
	) {
		parent::__construct();

		$this->roleRepository = $entityManager->getRepository(Role::class);
	}


	/**
	 * @return \Nette\Forms\Controls\TextInput
	 */
	public function addName()
	{
		return $this->addText('name', 'Name');
	}

	/**
	 * @return \Nette\Forms\Controls\SelectBox
	 */
	public function addMode()
	{
		return $this->addSelect('mode', 'Mode', RegistrationMode::getLabels());
	}

	/**
	 * @return \Nette\Forms\Controls\SelectBox
	 */
	public function addLoginProviderMode()
	{
		return $this->addSelect('loginProviderMode', 'Login provider mode', LoginProviderMode::getLabels());
	}

	/**
	 * @return \Nette\Forms\Controls\MultiSelectBox
	 */
	public function addRoles()
	{
		return $this->addMultiSelect('roles', 'Roles', $this->roleRepository->findPairs(array(), 'name', array(), 'id'));
	}

}
