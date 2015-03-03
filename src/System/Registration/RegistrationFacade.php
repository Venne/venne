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
use Venne\Security\SecurityManager;
use Venne\Utils\Object;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class RegistrationFacade extends Object
{

	/** @var \Doctrine\ORM\EntityManager */
	private $entityManager;

	/** @var \Kdyby\Doctrine\EntityRepository */
	private $registrationRepository;

	/** @var \Venne\Security\SecurityManager */
	private $securityManager;

	public function __construct(
		EntityManager $entityManager,
		SecurityManager $securityManager
	) {
		$this->entityManager = $entityManager;
		$this->registrationRepository = $entityManager->getRepository(Registration::class);
		$this->securityManager = $securityManager;
	}

	/**
	 * @param int $registrationId
	 * @return \Venne\System\Registration\Registration
	 */
	public function getById($registrationId)
	{
		$registration = $this->registrationRepository->find($registrationId);
		if ($registration === null) {
			throw new RegistrationNotFoundException($registrationId);
		}

		return $registration;
	}

	public function saveRegistration(Registration $registration)
	{
		$this->entityManager->persist($registration);
		$this->entityManager->flush($registration);
	}

	/**
	 * @return string[]
	 */
	public function getRegistrationOptions()
	{
		$values = array();

		foreach ($this->registrationRepository->findAll() as $registration) {
			$values[$registration->getId()] = $registration->getName();
		}

		return $values;
	}

}
