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
use Venne\System\Invitation\DTO;
use Venne\Utils\Object;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class InvitationFacade extends Object
{

	/** @var \Doctrine\ORM\EntityManager */
	private $entityManager;

	/** @var \Kdyby\Doctrine\EntityRepository */
	private $invitationRepository;

	public function __construct(
		EntityManager $entityManager
	) {
		$this->entityManager = $entityManager;
		$this->invitationRepository = $entityManager->getRepository(Invitation::class);
	}

	/**
	 * @param int $invitationId
	 * @return \Venne\System\Registration\Registration
	 */
	public function getById($invitationId)
	{
		$invitation = $this->invitationRepository->find($invitationId);
		if ($invitation === null) {
			throw new InvitationNotFoundException($invitationId);
		}

		return $invitation;
	}

	public function saveInvitation(Invitation $invitation)
	{
		$this->entityManager->persist($invitation);
		$this->entityManager->flush($invitation);
	}

}
