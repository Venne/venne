<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\System\AdminModule;

use Doctrine\ORM\EntityManager;
use Venne\DataTransfer\DataTransferManager;
use Venne\Security\Login;
use Venne\Security\User;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class DashboardPresenter extends \Nette\Application\UI\Presenter
{

	use \Venne\System\AdminPresenterTrait;

	/** @var \Kdyby\Doctrine\EntityRepository */
	private $logRepository;

	/** @var \Kdyby\Doctrine\EntityRepository */
	private $userRepository;

	/** @var \Venne\DataTransfer\DataTransferManager */
	private $dataTransferManager;

	public function __construct(
		EntityManager $entityManager,
		DataTransferManager $dataTransferManager
	)
	{
		$this->logRepository = $entityManager->getRepository(Login::class);
		$this->userRepository = $entityManager->getRepository(User::class);
		$this->dataTransferManager = $dataTransferManager;
	}

	public function renderDefault()
	{
		$this->template->userDto = $this->dataTransferManager
			->createQuery(UserDto::class, function () {
				return $this->userRepository->find($this->getUser()->getIdentity()->getId());
			})
			->enableCache(sprintf('#%s', $this->getUser()->getIdentity()->getId()))
			->fetch();
	}

}
