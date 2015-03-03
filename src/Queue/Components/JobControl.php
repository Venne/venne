<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Queue\Components;

use Doctrine\ORM\EntityManager;
use Nette\Application\BadRequestException;
use Nette\Security\User;
use Venne;
use Venne\DataTransfer\DataTransferManager;
use Venne\Queue\Job;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class JobControl extends Venne\System\UI\Control
{

	/** @var integer */
	private $id;

	/** @var \Doctrine\ORM\EntityManager */
	private $entityManager;

	/** @var \Nette\Security\User */
	private $user;

	/** @var \Kdyby\Doctrine\EntityRepository */
	private $jobRepository;

	/** @var \Venne\DataTransfer\DataTransferManager */
	private $dataTransferManager;

	/**
	 * @param int $id
	 * @param \Doctrine\ORM\EntityManager $entityManager
	 * @param \Nette\Security\User $user
	 * @param \Venne\DataTransfer\DataTransferManager $dataTransferManager
	 */
	public function __construct(
		$id,
		EntityManager $entityManager,
		User $user,
		DataTransferManager $dataTransferManager
	) {
		parent::__construct();

		$this->id = $id;
		$this->entityManager = $entityManager;
		$this->jobRepository = $entityManager->getRepository(Job::class);
		$this->user = $user;
		$this->dataTransferManager = $dataTransferManager;
	}

	public function handleRemove()
	{
		if (($entity = $this->jobRepository->find($this->id)) === null) {
			throw new BadRequestException;
		}

		if ($entity->user !== $this->user->identity) {
			throw new BadRequestException;
		}

		$this->entityManager->remove($entity);
		$this->entityManager->flush($entity);

		if (!$this->presenter->isAjax()) {
			$this->redirect('this');
		}
	}

	public function render()
	{
		$this->template->job = $this->jobRepository->find($this->id);
		$this->template->render();
	}

}
