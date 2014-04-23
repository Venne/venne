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

use Venne\Forms\IFormFactory;
use Venne\Security\SecurityManager;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class AuthenticationFormFactory implements IFormFactory
{

	/** @var IFormFactory */
	private $formFactory;

	/** @var SecurityManager */
	private $securityManager;

	/** @var array */
	private $registrations;


	/**
	 * @param IFormFactory $formFactory
	 * @param array $registrations
	 * @param SecurityManager $securityManager
	 */
	public function __construct(IFormFactory $formFactory, array $registrations, SecurityManager $securityManager)
	{
		$this->formFactory = $formFactory;
		$this->registrations = $registrations;
		$this->securityManager = $securityManager;
	}


	public function create()
	{
		$form = $this->formFactory->create();

		$form->addGroup('Authentication');
		$form->addSelect('autologin', 'Auto login')
			->setItems($this->securityManager->getLoginProviders(), FALSE)
			->setPrompt('Deactivated')
			->addCondition($form::EQUAL, '')
			->elseCondition()->toggle('form-autoregistration');

		$form->addGroup()->setOption('id', 'form-autoregistration');
		$form->addSelect('autoregistration', 'Auto registration')
			->setPrompt('Deactivated')
			->setItems(array_keys($this->registrations), FALSE);

		$forgotPassword = $form->addContainer('forgotPassword');
		$forgotPassword->setCurrentGroup($form->addGroup('Forgot password'));
		$enabled = $forgotPassword->addCheckbox('enabled', 'Enabled');
		$enabled->addCondition($form::EQUAL, TRUE)->toggle('form-reset');

		$forgotPassword->setCurrentGroup($form->addGroup()->setOption('container', \Nette\Utils\Html::el('div')->id('form-reset')));
		$forgotPassword->addText('emailSubject', 'Subject')
			->addConditionOn($enabled, $form::EQUAL, TRUE)
			->addRule($form::FILLED);
		$forgotPassword->addText('emailSender', 'Sender')
			->addConditionOn($enabled, $form::EQUAL, TRUE)
			->addRule($form::FILLED);
		$forgotPassword->addText('emailFrom', 'From')
			->addConditionOn($enabled, $form::EQUAL, TRUE)
			->addRule($form::FILLED)->addRule($form::EMAIL);
		$forgotPassword->addTextArea('emailText', 'Text')
			->addConditionOn($enabled, $form::EQUAL, TRUE)
			->addRule($form::FILLED);

		$form->setCurrentGroup();
		$form->addSubmit('_submit', 'Save');

		return $form;
	}

}
