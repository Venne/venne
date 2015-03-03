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
use Venne\Forms\IFormFactory;
use Venne\Security\SecurityManager;
use Venne\System\Registration\Registration;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class AuthenticationFormFactory implements \Venne\Forms\IFormFactory
{

	/** @var \Venne\Forms\IFormFactory */
	private $formFactory;

	/** @var \Venne\Security\SecurityManager */
	private $securityManager;

	/** @var \Kdyby\Doctrine\EntityRepository */
	private $registrationRepository;

	public function __construct(IFormFactory $formFactory, EntityManager $entityManager, SecurityManager $securityManager)
	{
		$this->formFactory = $formFactory;
		$this->registrationRepository = $entityManager->getRepository(Registration::class);
		$this->securityManager = $securityManager;
	}

	/**
	 * @return \Nette\Application\UI\Form
	 */
	public function create()
	{
		$form = $this->formFactory->create();

		$reg = array();
		foreach ($this->registrationRepository->findAll() as $registration) {
			$reg[$registration->getId()] = $registration->getName();
		}

		$form->addGroup('Authentication');
		$form->addSelect('autologin', 'Auto login')
			->setItems($this->securityManager->getLoginProviders(), false)
			->setPrompt('Deactivated')
			->addCondition($form::EQUAL, '')
			->elseCondition()->toggle('form-autoregistration');

		$form->addGroup()->setOption('id', 'form-autoregistration');
		$form->addSelect('autoregistration', 'Auto registration')
			->setPrompt('Deactivated')
			->setItems($reg);

		$form->setCurrentGroup();
		$form->addSubmit('_submit', 'Save');

		return $form;
	}

}
