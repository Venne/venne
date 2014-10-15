<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Security\AdminModule;

use Doctrine\ORM\EntityManager;
use Kdyby\Doctrine\Entities\BaseEntity;
use Kdyby\DoctrineForms\EntityFormMapper;
use Nette\Security\User;
use Venne\System\Invitation;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class InvitationFormService extends \Venne\System\DoctrineFormService
{

	/** @var \Nette\Security\User */
	private $netteUser;

	/** @var \Kdyby\Doctrine\EntityRepository */
	private $userRepository;

	public function __construct(
		InvitationFormFactory $formFactory,
		EntityManager $entityManager,
		EntityFormMapper $entityFormMapper,
		User $netteUser
	) {
		parent::__construct($formFactory, $entityManager, $entityFormMapper);
		$this->netteUser = $netteUser;
		$this->userRepository = $entityManager->getRepository(\Venne\Security\User::class);
	}

	/**
	 * @return string
	 */
	protected function getEntityClassName()
	{
		return Invitation::class;
	}

	/**
	 * @return \Kdyby\Doctrine\Entities\BaseEntity
	 */
	protected function createEntity()
	{
		$user = $this->userRepository->find($this->netteUser->getIdentity()->getId());

		$class = new \ReflectionClass($this->getEntityClassName());

		return $class->newInstanceArgs(array($user));
	}

}
