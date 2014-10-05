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
use Venne\Forms\IFormFactory;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class AdminFormFactory extends \Nette\Object implements \Venne\Forms\IFormFactory
{

	/** @var \Venne\Forms\IFormFactory */
	private $formFactory;

	public function __construct(IFormFactory $formFactory)
	{
		$this->formFactory = $formFactory;
	}

	/**
	 * @return \Nette\Application\UI\Form
	 */
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
		$user->addMultiSelect('roleEntities', 'Roles')
			->setOption(IComponentMapper::ITEMS_TITLE, 'name');
		$user->addText('key', 'Lock key')
			->setOption('description', 'If is set user cannot log in.');

		$user->setCurrentGroup($form->addGroup('Password'));
		$passwordNew = $user->addCheckbox('password_new', 'Change password');
		$passwordNew->addCondition($form::EQUAL, true)->toggle('setPasswd');
		$user->setCurrentGroup($form->addGroup()->setOption('container', 'fieldset id=setPasswd'));
		$user->addPassword('password', 'Password')
			->addConditionOn($passwordNew, Form::FILLED)
			->addRule(Form::FILLED, 'Enter password')
			->addRule(Form::MIN_LENGTH, 'Password is short', 5);
		$user->addPassword('password_confirm', 'Confirm password')
			->addConditionOn($passwordNew, Form::FILLED)
			->addRule(Form::EQUAL, 'Invalid re password', $user['password']);

		return $form;
	}

}
