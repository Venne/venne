<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Security\AdminModule;

use Venne\Forms\IFormFactory;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class AccountFormFactory implements IFormFactory
{

	/** @var IFormFactory */
	private $formFactory;


	/**
	 * @param IFormFactory $formFactory
	 */
	public function __construct(IFormFactory $formFactory)
	{
		$this->formFactory = $formFactory;
	}


	/**
	 * @return \Nette\Forms\Form
	 */
	public function create()
	{
		$form = $this->formFactory->create();

		$form->addGroup('Admin account');
		$form->addText('name', 'Name')
			->addRule($form::FILLED, 'Enter name');
		$form->addPassword('password', 'Password')
			->addRule($form::FILLED, 'Enter password')
			->addRule($form::MIN_LENGTH, 'Password is short', 5);
		$form->addPassword('_password', 'Confirm password')
			->addRule($form::EQUAL, 'Invalid re password', $form['password']);

		$form->setCurrentGroup();
		$form->addSubmit('_submit', 'Save');

		return $form;
	}

}
