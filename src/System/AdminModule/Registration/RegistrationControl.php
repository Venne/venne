<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\System\AdminModule\Registration;

use Venne\Forms\IFormFactory;
use Venne\Security\User\DefaultType\User;
use Venne\Security\SecurityManager;
use Venne\System\Registration\LoginProviderMode;
use Venne\System\Registration\Registration;
use Venne\System\Registration\Form\RegistrationContainerFactory;
use Venne\System\Registration\RegistrationFacade;
use Venne\System\Registration\RegistrationMapper;
use Venne\System\Registration\RegistrationMode;
use Venne\System\UI\Control;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 *
 * @method onSave(\Venne\System\AdminModule\Registration\RegistrationControl $control)
 */
class RegistrationControl extends Control
{

	/** @var \Closure[] */
	public $onSave = array();

	/** @var int|null */
	private $registrationId;

	/** @var \Venne\Forms\FormFactory */
	private $formFactory;

	/** @var \Venne\System\Registration\RegistrationFacade */
	private $registrationFacade;

	/** @var \Venne\System\Registration\Form\RegistrationContainerFactory */
	private $registrationContainerFactory;

	/** @var \Venne\System\Registration\RegistrationMapper */
	private $registrationMapper;

	/** @var \Venne\Security\SecurityManager */
	private $securityManager;

	/**
	 * @param int|null $registrationId
	 * @param \Venne\Forms\IFormFactory $formFactory
	 * @param \Venne\System\Registration\RegistrationFacade $registrationFacade
	 * @param \Venne\System\Registration\Form\RegistrationContainerFactory $registrationContainerFactory
	 * @param \Venne\System\Registration\RegistrationMapper $registrationMapper
	 * @param \Venne\Security\SecurityManager $securityManager
	 */
	public function __construct(
		$registrationId,
		IFormFactory $formFactory,
		RegistrationFacade $registrationFacade,
		RegistrationContainerFactory $registrationContainerFactory,
		RegistrationMapper $registrationMapper,
		SecurityManager $securityManager
	) {
		$this->registrationId = $registrationId !== null ? (int) $registrationId : null;
		$this->formFactory = $formFactory;
		$this->registrationFacade = $registrationFacade;
		$this->registrationContainerFactory = $registrationContainerFactory;
		$this->registrationMapper = $registrationMapper;
		$this->securityManager = $securityManager;
	}

	/**
	 * @return \Nette\Application\UI\Form
	 */
	protected function createComponentForm()
	{
		$registration = $this->registrationId !== null
			? $this->registrationFacade->getById($this->registrationId)
			: new Registration('', $this->securityManager->getUserTypeByClass(User::class), RegistrationMode::get(RegistrationMode::BASIC), LoginProviderMode::get(LoginProviderMode::LOAD));

		$form = $this->formFactory->create();
		$form['registration'] = $registrationContainer = $this->registrationContainerFactory->create();

		$registrationContainer->addName();
		$registrationContainer->addMode();
		$registrationContainer->addLoginProviderMode();
		$registrationContainer->addRoles();
		$registrationContainer->setDefaults($this->registrationMapper->load($registration));

		$form->addSubmit('_submit', 'Save')->onClick[] = function () use ($registration, $registrationContainer) {
			$this->registrationMapper->save($registration, $registrationContainer->getValues(true));
			$this->registrationFacade->saveRegistration($registration);

			$this->onSave($this);
			$this->redirect('this');
		};

		return $form;
	}

	public function render()
	{
		echo $this['form'];
	}

}
