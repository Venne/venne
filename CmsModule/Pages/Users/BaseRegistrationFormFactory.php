<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace CmsModule\Pages\Users;

use CmsModule\Content\IRegistrationFormFactory;
use CmsModule\Security\ILoginProvider;
use DoctrineModule\Forms\FormFactory;
use Nette\Utils\Strings;
use Venne\Forms\Form;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
abstract class BaseRegistrationFormFactory extends FormFactory implements IRegistrationFormFactory
{

	/**
	 * @param Form $form
	 */
	protected function configure(Form $form)
	{
		$user = $form->addOne('user');
		$user->setCurrentGroup($form->addGroup());
		$user->addText('email', 'E-mail')
			->addRule(Form::EMAIL, 'Enter email');

		$user->addPassword('password', 'Password')
			->setOption('description', 'minimal length is 5 char')
			->addRule(Form::FILLED, 'Enter password')
			->addRule(Form::MIN_LENGTH, 'Password is short', 5);
		$user->addPassword('password_confirm', 'Confirm password')
			->addRule(Form::EQUAL, 'Invalid re password', $user['password']);
	}


	public function handleAttached(Form $form)
	{
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
