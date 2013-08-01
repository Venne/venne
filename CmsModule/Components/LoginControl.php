<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace CmsModule\Components;

use CmsModule\Content\Control;
use CmsModule\Forms\LoginFormFactory;
use CmsModule\Security\SecurityManager;
use Nette\Forms\Form;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class LoginControl extends Control
{

	/** @var LoginFormFactory */
	protected $loginFormFactory;

	/** @var SecurityManager */
	protected $securityManager;


	/**
	 * @param LoginFormFactory $loginFormFactory
	 */
	public function injectLoginFormFactory(LoginFormFactory $loginFormFactory)
	{
		$this->loginFormFactory = $loginFormFactory;
	}


	/**
	 * @param SecurityManager $securityManager
	 */
	public function injectSecurityManager(SecurityManager $securityManager)
	{
		$this->securityManager = $securityManager;
	}


	protected function createComponentForm()
	{
		$this->loginFormFactory->setRedirect(NULL);

		$form = $this->loginFormFactory->invoke();
		$form->onSuccess[] = $this->formSuccess;
		return $form;
	}


	public function formSuccess(Form $form)
	{
		$this->redirect('this');
	}


	public function handleLogin($name)
	{
		$socialLogin = $this->securityManager->getSocialLoginByName($name);
		$data = $socialLogin->getData();

		if (!$data) {
			$this->presenter->redirectUrl($socialLogin->getLoginUrl());
		}

		try {
			$identity = $socialLogin->authenticate(array());
			$this->presenter->user->login($identity);
		} catch (\Nette\Security\AuthenticationException $e) {
			$this->getPresenter()->flashMessage($e->getMessage(), 'warning');
		}

		$this->redirect('this');
	}


	public function renderDefault()
	{
		$this->template->socialLogins = $this->securityManager->getSocialLogins();
	}


	public function handleLogout()
	{
		$this->presenter->user->logout(true);

		$this->redirect('this');
	}
}
