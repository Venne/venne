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

use DoctrineModule\Forms\FormFactory;
use Venne\Forms\Form;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class ConfirmFormFactory extends FormFactory
{

	/**
	 * @param Form $form
	 */
	public function configure(Form $form)
	{
		$form->addPassword('password', 'Password')
			->setOption('description', 'minimal length is 5 char')
			->addRule(Form::FILLED, 'Enter password')
			->addRule(Form::MIN_LENGTH, 'Password is short', 5);
		$form->addPassword('password_confirm', 'Confirm password')
			->addRule(Form::EQUAL, 'Invalid re password', $form['password']);


		$form->addSaveButton('Reset password')
			->getControlPrototype()->class[] = 'btn-primary';
	}


	public function handleSave(Form $form)
	{
		$form->data->removeResetKey();

		parent::handleSave($form);
	}

}
