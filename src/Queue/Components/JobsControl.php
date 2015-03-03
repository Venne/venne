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
use Nette\Application\UI\Multiplier;
use Nette\Security\User;
use Venne;
use Venne\DataTransfer\DataTransferManager;
use Venne\Queue\Job;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class JobsControl extends Venne\System\UI\Control
{

	/** @var integer */
	private $offset = 0;

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
	) {
		parent::__construct();

		$this->jobRepository = $entityManager->getRepository(Job::class);
		$this->user = $user;
		$this->jobControlFactory = $jobControlFactory;
		$this->dataTransferManager = $dataTransferManager;
	}

	/**
	 * @param int $offset
	 */
	public function handleLoad($offset)
	{
		$this->offset = (integer) $offset;
		$this->redrawControl('content');
		$this->redrawControl('js');
	}

	/**
	 * @return \Nette\Application\UI\Multiplier
	 */
	protected function createComponentJob()
	{
		return new Multiplier(function ($id) {
			return $this->jobControlFactory->create($id);
		});
	}

	public function render()
	{
		$this->template->jobs = $this->jobRepository->createQueryBuilder('a')
					->andWhere('a.user = :user')->setParameter('user', $this->user->getIdentity()->getId())
					->orderBy('a.date', 'ASC')
					->setMaxResults(5)
					->setFirstResult($this->offset)
					->getQuery()->getResult();
		$this->template->jobCount = $this->jobRepository->createQueryBuilder('a')
			->select('COUNT(a.id)')
			->andWhere('a.user = :user')->setParameter('user', $this->user->getIdentity()->getId())
			->getQuery()->getSingleScalarResult();
		$this->template->offset = $this->offset + 5;

		$this->template->render();
	}

}
