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
use Venne\Forms\FormFactory;
use Venne\Forms\Form;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class LoginFormFactory extends FormFactory
{


	/** @var string */
	protected $redirect = 'this';


	/**
	 * @param string $redirect
	 */
	public function setRedirect($redirect)
	{
		$this->redirect = $redirect;
	}


	/**
	 * @param Form $form
	 */
	public function configure(Form $form)
	{
		$form->addText('username', 'Login')->setRequired('Please provide a username.');
		$form->addPassword('password', 'Password')->setRequired('Please provide a password.');
		$form->addCheckbox('remember', 'Remember me on this computer');
		$form->addSaveButton("Sign in")->getControlPrototype()->class[] = 'btn-primary';
	}


	public function handleSuccess($form)
	{
		try {
			$values = $form->getValues();
			$form->presenter->user->login($values->username, $values->password);

			if ($values->remember) {
				$form->presenter->user->setExpiration('+ 14 days', FALSE);
			} else {
				$form->presenter->user->setExpiration('+ 20 minutes', TRUE);
			}

			$form->presenter->restoreRequest($form->presenter->backlink);
			if ($this->redirect) {
				$form->presenter->redirect($this->redirect . ':');
			}
		} catch (\Nette\Security\AuthenticationException $e) {
			$form->getPresenter()->flashMessage($e->getMessage(), "warning");
		}
	}
}
