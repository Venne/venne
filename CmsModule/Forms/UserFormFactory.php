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

use Venne;
use Venne\Forms\Form;
use DoctrineModule\Forms\FormFactory;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class UserFormFactory extends FormFactory
{


	/**
	 * @param Form $form
	 */
	protected function configure(Form $form)
	{
		$group = $form->addGroup();
		$form->addCheckbox("enable", "Enable")->setDefaultValue(true);
		$form->addText("email", "E-mail")
			->addRule(Form::EMAIL, "Enter email");

		$form->addText("key", "Authentization key")->setOption("description", "If is set user cannot log in.");

		$form->addCheckbox("password_new", "Set password")->addCondition($form::EQUAL, true)->toggle('setPasswd');
		$form->addGroup()->setOption('id', 'setPasswd');
		$form->addPassword("password", "Password")
			->setOption("description", "minimal length is 5 char")
			->addConditionOn($form['password_new'], Form::FILLED)
			->addRule(Form::FILLED, 'Enter password')
			->addRule(Form::MIN_LENGTH, 'Password is short', 5);
		$form->addPassword("password_confirm", "Confirm password")
			->addConditionOn($form['password_new'], Form::FILLED)
			->addRule(Form::EQUAL, 'Invalid re password', $form['password']);

		$form->addGroup("Next informations");
		$form->addManyToMany("roleEntities", 'Roles');

		$form->addSaveButton('Save');
	}


	public function handleCatchError(Form $form, \DoctrineModule\SqlException $e)
	{
		if ($e->getCode() == '23000') {
			$form->addError("User is not unique");
			return true;
		}
	}


	public function handleSave(Form $form)
	{
		if ($form['password_new']->value) {
			$form->data->setPassword($form['password']->value);
		}

		parent::handleSave($form);
	}


	public function handleSuccess(Form $form)
	{
		$form->getPresenter()->flashMessage('User has been saved', 'success');
	}
}
