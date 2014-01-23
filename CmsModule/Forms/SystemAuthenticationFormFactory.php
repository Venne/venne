<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace CmsModule\Forms;

use CmsModule\Security\SecurityManager;
use FormsModule\Mappers\ConfigMapper;
use Venne\Forms\Form;
use Venne\Forms\FormFactory;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class SystemAuthenticationFormFactory extends FormFactory
{

	/** @var ConfigMapper */
	protected $mapper;

	/** @var SecurityManager */
	private $securityManager;

	/** @var array */
	private $registrations;


	/**
	 * @param $registrations
	 * @param ConfigMapper $mapper
	 * @param SecurityManager $securityManager
	 */
	public function __construct($registrations, ConfigMapper $mapper, SecurityManager $securityManager)
	{
		$this->registrations = $registrations;
		$this->mapper = $mapper;
		$this->securityManager = $securityManager;
	}


	protected function getMapper()
	{
		$mapper = clone $this->mapper;
		$mapper->setRoot('cms.administration.authentication');
		return $mapper;
	}


	/**
	 * @param Form $form
	 */
	protected function configure(Form $form)
	{
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

		$forgotPassword->setCurrentGroup($form->addGroup()->setOption('id', 'form-reset'));
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
		$form->addSaveButton('Save');
	}

}
