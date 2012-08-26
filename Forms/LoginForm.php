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

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class LoginForm extends \Venne\Application\UI\Form
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


	public function startup()
	{
		$this->addText('username', 'Login')->setRequired('Please provide a username.');
		$this->addPassword('password', 'Password')->setRequired('Please provide a password.');
		$this->addCheckbox('remember', 'Remember me on this computer');
		$this->addSubmit("_submit", "Sign in")->getControlPrototype()->class[] = 'btn btn-primary';
	}


	public function handleSuccess()
	{
		try {
			$values = $this->getValues();
			$this->presenter->user->login($values->username, $values->password);

			if ($values->remember) {
				$this->presenter->user->setExpiration('+ 14 days', FALSE);
			} else {
				$this->presenter->user->setExpiration('+ 20 minutes', TRUE);
			}

			$this->presenter->restoreRequest($this->presenter->backlink);
			if ($this->redirect) {
				$this->presenter->redirect($this->redirect . ':');
			}
		} catch (\Nette\Security\AuthenticationException $e) {
			$this->getPresenter()->flashMessage($e->getMessage(), "warning");
		}
	}
}
