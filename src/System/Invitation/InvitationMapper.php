<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\System\Invitation;

use Doctrine\ORM\EntityManager;
use Venne\Mapping\InvalidArgument;
use Venne\Security\NetteUser;
use Venne\Security\User\User;
use Venne\System\Registration\RegistrationFacade;
use Venne\Utils\Object;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class InvitationMapper extends Object
{

	/** @var \Kdyby\Doctrine\EntityRepository  */
	private $userRepository;

	/** @var \Venne\System\Registration\RegistrationFacade */
	private $registrationFacade;

	/** @var \Venne\Security\NetteUser */
	private $netteUser;

	public function __construct(
		EntityManager $entityManager,
		RegistrationFacade $registrationFacade,
		NetteUser $netteUser
	)
	{
		$this->userRepository = $entityManager->getRepository(User::class);
		$this->registrationFacade = $registrationFacade;
		$this->netteUser = $netteUser;
	}


	/**
	 * @param \Venne\System\Invitation\Invitation $invitation
	 * @return mixed[]
	 */
	public function load($invitation)
	{
		if (!$invitation instanceof Invitation) {
			throw new InvalidArgument();
		}

		return array(
			'id' => $invitation->getId(),
			'author' => $invitation->getAuthor()->getId(),
			'registration' => $invitation->getRegistration()->getId(),
			'email' => $invitation->getEmail(),
		);
	}

	/**
	 * @param mixed[] $values
	 * @return \Venne\System\Invitation\Invitation
	 */
	public function create(array $values)
	{
		return new Invitation(
			$this->userRepository->find($this->netteUser->getIdentity()->getId()),
			$this->registrationFacade->getById($values['registration']),
			$values['email']
		);
	}

}
