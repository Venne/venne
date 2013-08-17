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

use CmsModule\Security\AuthorizatorFactory;
use CmsModule\Security\Identity;
use CmsModule\Security\SecurityManager;
use Nette\Security\AuthenticationException;
use Venne\Forms\Form;
use Venne\Forms\FormFactory;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class LoginFormFactory extends FormFactory
{

	/** @var string */
	protected $redirect = 'this';

	/** @var SecurityManager */
	private $securityManager;

	/** @var AuthorizatorFactory */
	private $authorizatorFactory;


	/**
	 * @param AuthorizatorFactory $authorizatorFactory
	 * @param SecurityManager $securityManager
	 */
	public function __construct(AuthorizatorFactory $authorizatorFactory, SecurityManager $securityManager)
	{
		$this->authorizatorFactory = $authorizatorFactory;
		$this->securityManager = $securityManager;
	}


	/**
	 * @param string $redirect
	 */
	public function setRedirect($redirect)
	{
		$this->redirect = $redirect;
	}


	public function handleLogin($form, $name)
	{
		$socialLogin = $this->securityManager->getSocialLoginByName($name);
		$data = $socialLogin->getData();

		if (!$data) {
			$form->presenter->redirectUrl($socialLogin->getLoginUrl());
		}

		$this->authorizatorFactory->clearPermissionSession();

		try {
			$identity = $socialLogin->authenticate(array());
			$form->presenter->user->login(new Identity($identity->email, $identity->roles));
		} catch (AuthenticationException $e) {
			$form->getPresenter()->flashMessage($e->getMessage(), 'warning');
		}
	}


	/**
	 * @param Form $form
	 */
	public function configure(Form $form)
	{
		$_this = $this;
		$form->addText('username', 'Login')->setRequired('Please provide a username.');
		$form->addPassword('password', 'Password')->setRequired('Please provide a password.');
		$form->addCheckbox('remember', 'Remember me on this computer');
		$form->addSaveButton('Sign in')->getControlPrototype()->class[] = 'btn-primary';

		foreach ($this->securityManager->getSocialLogins() as $socialLogin) {
			$form->addSubmit('_submit_' . $socialLogin, $socialLogin)->onClick[] = function ($button) use ($_this, $socialLogin) {
				$_this->handleLogin($button->form, $socialLogin);
			};
		}
	}


	public function handleSuccess(Form $form)
	{
		try {
			$values = $form->getValues();

			if ($form->isSubmitted() === $form->getSaveButton()) {
				$form->presenter->user->login($values->username, $values->password);
			}

			if ($values->remember) {
				$form->presenter->user->setExpiration('+ 14 days', FALSE);
			} else {
				$form->presenter->user->setExpiration('+ 20 minutes', TRUE);
			}

			$this->doRedirect($form);
		} catch (AuthenticationException $e) {
			$form->getPresenter()->flashMessage($e->getMessage(), 'warning');
		}
	}


	private function doRedirect($form)
	{
		if ($this->redirect) {
			$form->presenter->restoreRequest($form->presenter->backlink);
			$form->presenter->redirect($this->redirect . ':');
		}
	}
}
