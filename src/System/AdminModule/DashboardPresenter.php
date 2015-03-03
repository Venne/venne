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
use Venne\Security\Login\Login;
use Venne\Security\Role\Role;
use Venne\Security\User\User;
use Venne\System\AdminModule\Registration\RegistrationControlFactory;

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

	/** @var \Venne\System\AdminModule\Registration\RegistrationControlFactory */
	private $registrationControlFactory;

	public function __construct(
		EntityManager $entityManager,
		DataTransferManager $dataTransferManager,
		RegistrationControlFactory $registrationControlFactory
	)
	{
		$this->logRepository = $entityManager->getRepository(Login::class);
		$this->userRepository = $entityManager->getRepository(User::class);
		$this->dataTransferManager = $dataTransferManager;
		$this->registrationControlFactory = $registrationControlFactory;
	}

	public function renderDefault()
	{
		$this->template->user = $this->userRepository->find($this->getUser()->getIdentity()->getId());
	}

	/**
	 * @return \Venne\System\AdminModule\Registration\RegistrationControl
	 */
	protected function createComponentRegistration()
	{
		$control = $this->registrationControlFactory->create(1);
		$control->onSave[] = function () {
			$this->flashMessage($this->getTranslator()->translate('Registration has been saved'));
		};

		return $control;
	}

}
