<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace CmsModule\Content\Forms;

use Venne;
use Venne\Forms\Form;
use DoctrineModule\Forms\FormFactory;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class RegistrationFrontFormFactory extends FormFactory
{


	/**
	 * @param Form $form
	 */
	public function configure(Form $form)
	{
		$form->addGroup("Registration");


		$form->addText('email', 'E-mail')->addRule($form::EMAIL);
		$form->addPassword('password', 'Password');
		$form->addPassword("password_confirm", "Confirm password")
			->addRule(Form::EQUAL, 'Invalid re password', $form['password']);

		$form->setCurrentGroup();
		$form->addSaveButton('Register');
	}


	public function handleSave(Form $form)
	{
		$form->data->setPassword($form['password']->value);

		parent::handleSave($form);
	}


	public function handleCatchError(Form $form, $e)
	{
		if ($e->getCode() == '23000' && strpos($e->getMessage(), 'Duplicate entry') !== false) {
			$form->addError("User with email {$form['email']->value} already exists");
			return true;
		}
	}
}
