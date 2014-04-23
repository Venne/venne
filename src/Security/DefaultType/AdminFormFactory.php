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

use Kdyby\DoctrineForms\IComponentMapper;
use Nette\Forms\Form;
use Nette\Object;
use Venne\Forms\IFormFactory;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class AdminFormFactory extends Object implements IFormFactory
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
		$user->addCheckbox('published', 'Enable');
		$user->addText('email', 'E-mail')
			->addRule(Form::EMAIL, 'Enter email');
		$user->addText('name', 'Name');
		$user->addTextArea('notation', 'Notation', 40, 4)
			->getControlPrototype()->attrs['class'] = 'input-block-level';

		$route = $user->addContainer('route');
		$route->setCurrentGroup($group);
		//$route->addFileEntityInput('photo', 'Avatar');

		$user->setCurrentGroup($form->addGroup('Block by key'));
		$user->addCheckbox('key_new', 'Enable')->addCondition($form::EQUAL, TRUE)->toggle('setKey');
		$user->setCurrentGroup($form->addGroup()->setOption('id', 'setKey'));
		$user->addText('key', 'Authentization key')->setOption('description', 'If is set user cannot log in.');

		$user->setCurrentGroup($form->addGroup('Password'));
		$user->addCheckbox('password_new', 'Change password')->addCondition($form::EQUAL, TRUE)->toggle('setPasswd');
		$user->setCurrentGroup($form->addGroup()->setOption('id', 'setPasswd'));
		$user->addText('password', 'Password')
			->addConditionOn($user['password_new'], Form::FILLED)
			->addRule(Form::FILLED, 'Enter password')
			->addRule(Form::MIN_LENGTH, 'Password is short', 5);
		$user->addText('password_confirm', 'Confirm password')
			->addConditionOn($user['password_new'], Form::FILLED)
			->addRule(Form::EQUAL, 'Invalid re password', $user['password']);

		$user->setCurrentGroup($form->addGroup('Next informations'));
		//$user->addManyToMany('roleEntities', 'Roles');
		$user->addMultiSelect('roleEntities', 'Roles')
			->setOption(IComponentMapper::ITEMS_TITLE, 'name');

		$form->setCurrentGroup();
		$form->addSubmit('_submit', 'Save');

		$form->onValidate[] = $this->handleSave;
		return $form;
	}


//	public function handleCatchError(Form $form, $e)
//	{
//		if ($e instanceof DBALException && $e->getCode() == '23000') {
//			$form->addError('User is not unique');
//			return TRUE;
//		}
//	}
//
//
//	public function handleAttached(Form $form)
//	{
//		$form->setCurrentGroup();
//		$form->addSaveButton('Save');
//
//		if ($form->isSubmitted()) {
//			if (!$form['user']['password_new']->value) {
//				unset($form['user']['password']);
//			}
//		}
//	}
//
//
//	public function handleLoad(Form $form)
//	{
//		if ($form->data->user->key) {
//			$form['user']['key_new']->value = TRUE;
//		}
//	}


	public function handleSave(Form $form)
	{
		if (!$form['user']['password_new']->value) {
			$form['user']['password']->value = NULL;
		}
		if (!$form['user']['key_new']->value) {
			$form['user']['key']->value = NULL;
		}

	}
}
