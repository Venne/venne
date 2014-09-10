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
use Nette\Application\BadRequestException;
use Nette\Security\User;
use Venne\DataTransfer\DataTransferManager;
use Venne\Queue\JobDto;
use Venne\Queue\JobEntity;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class JobControl extends \Venne\System\UI\Control
{

	/** @var \Nette\Security\User */
	private $user;

	/** @var \Kdyby\Doctrine\EntityDao */
	private $jobDao;

	/** @var \Venne\DataTransfer\DataTransferManager */
	private $dataTransferManager;

	public function __construct(
		EntityDao $jobDao,
		User $user,
		DataTransferManager $dataTransferManager
	)
	{
		parent::__construct();

		$this->jobDao = $jobDao;
		$this->user = $user;
		$this->dataTransferManager = $dataTransferManager;
	}

	/**
	 * @param int $id
	 */
	public function handleRemove($id)
	{
		if (($entity = $this->jobDao->find($id)) === null) {
			throw new BadRequestException;
		}

		if ($entity->user !== $this->user->identity) {
			throw new BadRequestException;
		}

		$this->jobDao->delete($entity);

		if (!$this->presenter->isAjax()) {
			$this->redirect('this');
		}
	}

	public function render($id)
	{
		$this->template->job = $this->dataTransferManager
			->createQuery(JobDto::getClassName(), function () use ($id) {
				return $this->jobDao->find($id);
			})
			->enableCache()
			->fetch();
		$this->template->render();
	}

}
