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
use Nette\Security\User;
use Venne\DataTransfer\DataTransferManager;
use Venne\Queue\JobDto;
use Venne\Queue\Job;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class JobsControl extends \Venne\System\UI\Control
{

	/** @var \Nette\Security\User */
	private $user;

	/** @var \Kdyby\Doctrine\EntityRepository */
	private $jobRepository;

	/** @var \Venne\Queue\Components\IJobControlFactory */
	private $jobControlFactory;

	/** @var \Venne\DataTransfer\DataTransferManager */
	private $dataTransferManager;

	public function __construct(
		EntityManager $entityManager,
		User $user,
		IJobControlFactory $jobControlFactory,
		DataTransferManager $dataTransferManager
	)
	{
		parent::__construct();

		$this->jobRepository = $entityManager->getRepository(Job::class);
		$this->user = $user;
		$this->jobControlFactory = $jobControlFactory;
		$this->dataTransferManager = $dataTransferManager;
	}

	/**
	 * @return \Venne\Queue\Components\JobControl
	 */
	protected function createComponentJob()
	{
		return $this->jobControlFactory->create();
	}

	public function render()
	{
		$this->template->jobs = $this->dataTransferManager
			->createQuery(JobDto::class, function () {
				return $this->jobRepository->createQueryBuilder('a')
					->andWhere('a.user = :user')->setParameter('user', $this->user->getIdentity()->getId())
					->orderBy('a.date', 'ASC')
					->getQuery()->getResult();
			})
			->enableCache()
			->fetchAll();
		$this->template->render();
	}

}
