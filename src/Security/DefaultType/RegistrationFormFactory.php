<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Security\DefaultType;

use Nette\Forms\Form;
use Nette\Utils\Strings;
use Venne\Forms\IFormFactory;
use Venne\Security\ILoginProvider;
use Venne\Security\IRegistrationForm;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class RegistrationFormFactory implements IRegistrationForm, IFormFactory
{

	/** @var IFormFactory */
	private $formFactory;


	public function __construct(IFormFactory $formFactory)
	{
		$this->formFactory = $formFactory;
	}


	public function create()
	{
		$form = $this->formFactory->create();

		$user = $form->addContainer('user');
		$user->setCurrentGroup($form->addGroup());
		$user->addText('email', 'E-mail')
			->addRule(Form::EMAIL, 'Enter email');

		$user->addPassword('password', 'Password')
			->addRule(Form::FILLED, 'Enter password')
			->addRule(Form::MIN_LENGTH, 'Password is short', 5);
		$user->addPassword('password_confirm', 'Confirm password')
			->addRule(Form::EQUAL, 'Invalid re password', $user['password']);

		$form->setCurrentGroup();
		$form->addSubmit('_submit', 'Register');

		return $form;
	}


	public function handleAttached(Form $form)
	{
		$form->setCurrentGroup();
		$form->addSaveButton('Sign up');
	}


	/**
	 * @param Form $form
	 * @param ILoginProvider $loginProvider
	 */
	public function connectWithLoginProvider(Form $form, ILoginProvider $loginProvider)
	{
		$loginProviderEntity = $loginProvider->getLoginProviderEntity();

		$form['user']['email']->setValue($loginProviderEntity->email);
		$form['user']['password_confirm']->value = $form['user']['password']->value = Strings::random();
	}
}
