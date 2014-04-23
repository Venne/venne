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

use Venne\Forms\Form;
use Venne\Forms\IFormFactory;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class FrontFormFactory implements IFormFactory
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
		$group = $form->addGroup();
		$user->setCurrentGroup($group);
		$user->addText('name', 'Name');
		$user->addTextArea('notation', 'Notation', 40, 4)
			->getControlPrototype()->attrs['class'] = 'input-block-level';

		//$route = $user->addOne('route');
		//$route->setCurrentGroup($group);
		//$route->addFileEntityInput('photo', 'Avatar');

		$user->setCurrentGroup($form->addGroup('Password'));
		$user->addCheckbox('password_new', 'Change password')->addCondition($form::EQUAL, TRUE)->toggle('setPasswd');
		$user->setCurrentGroup($form->addGroup()->setOption('id', 'setPasswd'));
		$user->addPassword('password', 'Password')
			->addConditionOn($user['password_new'], $form::FILLED)
			->addRule($form::FILLED, 'Enter password')
			->addRule($form::MIN_LENGTH, 'Password is short', 5);
		$user->addPassword('password_confirm', 'Confirm password')
			->addConditionOn($user['password_new'], $form::FILLED)
			->addRule($form::EQUAL, 'Invalid re password', $user['password']);

		$form->setCurrentGroup();
		$form->addSubmit('_submit', 'Save');

		return $form;
	}


	public function handleAttached(Form $form)
	{
		$form->setCurrentGroup();
		$form->addSaveButton('Save');

		if ($form->isSubmitted()) {
			if (!$form['user']['password_new']->value) {
				unset($form['user']['password']);
			}
		}
	}


	public function handleSave(Form $form)
	{
		if ($form['user']['password_new']->value) {
			$form->data->user->setPassword($form['user']['password']->value);
		}

		parent::handleSave($form);
	}
}
