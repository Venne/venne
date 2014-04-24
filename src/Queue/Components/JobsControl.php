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

use Kdyby\Doctrine\EntityDao;
use Nette\Security\User;
use Venne\System\UI\Control;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class JobsControl extends Control
{

	/** @var User */
	private $user;

	/** @var EntityDao */
	private $jobDao;

	/** @var IJobControlFactory */
	private $jobControlFactory;


	/**
	 * @param EntityDao $jobDao
	 * @param User $user
	 * @param IJobControlFactory $jobControlFactory
	 */
	public function __construct(EntityDao $jobDao, User $user, IJobControlFactory $jobControlFactory)
	{
		parent::__construct();

		$this->jobDao = $jobDao;
		$this->user = $user;
		$this->jobControlFactory = $jobControlFactory;
	}


	/**
	 * @return int
	 */
	public function countJobs()
	{
		return $this->jobDao->createQueryBuilder('a')
			->select('COUNT(a.id)')
			->andWhere('a.user = :user')->setParameter('user', $this->user->identity)
			->getQuery()->getSingleScalarResult();
	}


	/**
	 * @return int
	 */
	public function getJobs()
	{
		return $this->jobDao->createQueryBuilder('a')
			->andWhere('a.user = :user')->setParameter('user', $this->user->identity)
			->orderBy('a.date', 'ASC')
			->getQuery()->getResult();
	}


	protected function createComponentJob()
	{
		return $this->jobControlFactory->create();
	}


	public function render()
	{
		$this->template->render();
	}

}
