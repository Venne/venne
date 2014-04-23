<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Security\Login;

use Venne\Forms\IFormFactory;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class ConfirmFormFactory implements IFormFactory
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

		$form->addPassword('password', 'Password')
			->setOption('description', 'minimal length is 5 char')
			->addRule($form::FILLED, 'Enter password')
			->addRule($form::MIN_LENGTH, 'Password is short', 5);
		$form->addPassword('password_confirm', 'Confirm password')
			->addRule($form::EQUAL, 'Invalid re password', $form['password']);


		$form->addSubmit('_submit', 'Reset password')
			->getControlPrototype()->class[] = 'btn-primary';

		return $form;
	}

}
