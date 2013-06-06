<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace CmsModule\Content\Presenters;

use CmsModule\Forms\LoginFormFactory;
use CmsModule\Security\SecurityManager;
use Nette\Forms\Form;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class LoginPresenter extends PagePresenter
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
		if ($this->page->page) {
			$this->redirect('this', array('route' => $this->page->page->mainRoute));
		}
		$this->redirect('this');
	}


	public function handleLogin($name)
	{
		$socialLogin = $this->securityManager->getSocialLoginByName($name);
		$data = $socialLogin->getData();

		if (!$data) {
			$this->redirectUrl($socialLogin->getLoginUrl());
		}


		$identity = $socialLogin->authenticate(array());
		if ($identity) {
			$this->user->login($identity);
		} else if ($this->page->registration) {
			$this->redirect('this', array('do' => 'load', 'name' => $name, 'route' => $this->page->registration->mainRoute));
		}

		$this->redirect('this');
	}


	public function  renderDefault()
	{
		$this->template->socialLogins = $this->securityManager->getSocialLogins();
	}
}
