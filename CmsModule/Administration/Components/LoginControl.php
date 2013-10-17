<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace CmsModule\Administration\Components;

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
	 * @param SecurityManager $securityManager
	 */
	public function __construct(LoginFormFactory $loginFormFactory, SecurityManager $securityManager)
	{
		parent::__construct();

		$this->loginFormFactory = $loginFormFactory;
		$this->securityManager = $securityManager;
	}


	protected function createComponentForm()
	{
		$_this = $this;
		$this->loginFormFactory->setRedirect(NULL);

		$form = $this->loginFormFactory->invoke();

		foreach ($this->securityManager->getSocialLogins() as $socialLogin) {
			$form['socialButtons']['_submit_' . $socialLogin]->onClick[] = function () use ($_this, $socialLogin) {
				$_this->handleLogin($socialLogin);
			};
		}

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
			$this->presenter->absoluteUrls = TRUE;
			$socialLogin->setRedirectUri($this->link('login', array($name)));
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
}
