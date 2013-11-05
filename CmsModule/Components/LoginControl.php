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
use CmsModule\Forms\ProviderFormFactory;
use CmsModule\Security\SecurityManager;
use Nette\Security\AuthenticationException;
use Venne\Forms\Form;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class LoginControl extends Control
{

	/** @var array */
	public $onSuccess;

	/** @persistent */
	public $provider;

	/** @var LoginFormFactory */
	protected $loginFormFactory;

	/** @var ProviderFormFactory */
	protected $providerFormFactory;

	/** @var SecurityManager */
	protected $securityManager;


	/**
	 * @param LoginFormFactory $loginFormFactory
	 * @param ProviderFormFactory $providerFormFactory
	 * @param SecurityManager $securityManager
	 */
	public function __construct(LoginFormFactory $loginFormFactory, ProviderFormFactory $providerFormFactory, SecurityManager $securityManager)
	{
		parent::__construct();

		$this->loginFormFactory = $loginFormFactory;
		$this->providerFormFactory = $providerFormFactory;
		$this->securityManager = $securityManager;
	}


	/**
	 * @return SecurityManager
	 */
	public function getSecurityManager()
	{
		return $this->securityManager;
	}


	protected function createComponentForm()
	{
		$_this = $this;

		$form = $this->loginFormFactory->invoke();

		foreach ($this->securityManager->getLoginProviders() as $loginProvider) {
			$form['socialButtons'][str_replace(' ', '_', $loginProvider)]->onClick[] = function () use ($_this, $loginProvider) {
				$_this->redirect('login!', $loginProvider);
			};
		}

		$form->onSuccess[] = $this->formSuccess;
		return $form;
	}


	protected function createComponentProviderForm()
	{
		$_this = $this;
		$this->providerFormFactory->setProvider($this->provider);

		$form = $this->providerFormFactory->invoke();
		$form['cancel']->onClick[] = function () use ($_this) {
			$_this->redirect('this', array('provider' => NULL));
		};
		$form->onSuccess[] = $this->providerFormSuccess;
		return $form;
	}


	public function formSuccess(Form $form)
	{
		$values = $form->getValues();
		$button = $form->isSubmitted();

		if ($values->remember) {
			$form->presenter->user->setExpiration('+ 14 days', FALSE);
		} else {
			$form->presenter->user->setExpiration('+ 20 minutes', TRUE);
		}

		if ($button === $form->getSaveButton()) {
			try {
				$form->presenter->user->login($values->username, $values->password);
			} catch (AuthenticationException $e) {
				$form->getPresenter()->flashMessage($form->presenter->translator->translate($e->getMessage()), 'warning');
			}

		} else {
			$this->redirect('login!', str_replace('_', ' ', $button->name));
		}

		$this->onSuccess($this);
	}


	public function providerFormSuccess(Form $form)
	{
		$this->redirect('login', array($form['provider']->value, json_encode((array)$form['parameters']->values)));
	}


	public function handleLogin($name, $parameters = NULL)
	{
		$login = $this->securityManager->getLoginProviderByName($name);

		if (($container = $login->getFormContainer()) !== NULL && $parameters == NULL) {
			$this->redirect('this', array('provider' => $name));

		} else {
			if ($parameters) {
				$parameters = json_decode($parameters, TRUE);
			}

			$this->authenticate($name, $parameters);
		}

		$this->onSuccess($this);
	}


	public function handleLogout()
	{
		$this->presenter->user->logout(true);

		$this->redirect('this');
	}


	private function authenticate($provider, $parameters = NULL)
	{
		$login = $this->securityManager->getLoginProviderByName($provider);

		try {
			if ($parameters) {
				$login->setAuthenticationParameters($parameters);
			}
			$identity = $login->authenticate(array());
			$this->presenter->user->login($identity);
		} catch (AuthenticationException $e) {
			$this->getPresenter()->flashMessage($this->getPresenter()->translator->translate($e->getMessage()), 'warning');
		}

		$this->redirect('this');
	}

}
