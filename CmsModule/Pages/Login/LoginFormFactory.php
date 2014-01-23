<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace CmsModule\Pages\Login;

use DoctrineModule\Forms\FormFactory;
use Venne\Forms\Form;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class LoginFormFactory extends FormFactory
{

	/**
	 * @param Form $form
	 */
	public function configure(Form $form)
	{
		$form->addGroup('Settings');
		$form->addManyToOne('registration', 'Register by');

		$form->addGroup('Forgot password');
		$enabled = $form->addCheckbox('resetEnabled', 'Enabled');
		$enabled->addCondition($form::EQUAL, TRUE)->toggle('form-reset');

		$form->addGroup()->setOption('id', 'form-reset');
		$form->addText('resetSubject', 'Subject')
			->addConditionOn($enabled, $form::EQUAL, TRUE)
			->addRule($form::FILLED);
		$form->addText('resetSender', 'Sender')
			->addConditionOn($enabled, $form::EQUAL, TRUE)
			->addRule($form::FILLED);
		$form->addText('resetFrom', 'From')
			->addConditionOn($enabled, $form::EQUAL, TRUE)
			->addRule($form::FILLED)->addRule($form::EMAIL);
		$form->addTextArea('resetText', 'Text')
			->addConditionOn($enabled, $form::EQUAL, TRUE)
			->addRule($form::FILLED);

		$form->setCurrentGroup();
		$form->addSaveButton('Save');
	}
}
