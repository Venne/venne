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

use Kdyby\Doctrine\EntityDao;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Form;
use Nette\Mail\IMailer;
use Nette\Security\AuthenticationException;
use Venne\Bridges\Kdyby\DoctrineForms\FormFactoryFactory;
use Venne\Security\AdminModule\ProviderFormFactory;
use Venne\Security\SecurityManager;
use Venne\Security\UserEntity;
use Venne\System\AdminModule\LoginFormFactory;
use Venne\System\UI\Control;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class LoginControl extends Control
{

	/** @var array */
	public $onSuccess;

	/** @var array */
	public $onError;

	/** @persistent */
	public $provider;

	/** @persistent */
	public $reset;

	/** @persistent */
	public $key;

	/** @var LoginFormFactory */
	private $loginFormFactory;

	/** @var ProviderFormFactory */
	private $providerFormFactory;

	/** @var ResetFormFactory */
	private $resetFormFactory;

	/** @var ConfirmFormFactory */
	private $confirmFormFactory;

	/** @var SecurityManager */
	private $securityManager;

	/** @var EntityDao */
	private $userDao;

	/** @var IMailer */
	private $mailer;

	/** @var FormFactoryFactory */
	private $formFactoryFactory;


	/**
	 * @param EntityDao $userDao
	 * @param LoginFormFactory $loginFormFactory
	 * @param ProviderFormFactory $providerFormFactory
	 * @param ResetFormFactory $resetFormFactory
	 * @param ConfirmFormFactory $confirmFormFactory
	 * @param SecurityManager $securityManager
	 * @param IMailer $mailer
	 * @param FormFactoryFactory $formFactoryFactory
	 */
	public function __construct(
		EntityDao $userDao,
		LoginFormFactory $loginFormFactory,
		ProviderFormFactory $providerFormFactory,
		ResetFormFactory $resetFormFactory,
		ConfirmFormFactory $confirmFormFactory,
		SecurityManager $securityManager,
		IMailer $mailer,
		FormFactoryFactory $formFactoryFactory
	)
	{
		parent::__construct();

		$this->loginFormFactory = $loginFormFactory;
		$this->providerFormFactory = $providerFormFactory;
		$this->resetFormFactory = $resetFormFactory;
		$this->confirmFormFactory = $confirmFormFactory;
		$this->securityManager = $securityManager;
		$this->userDao = $userDao;
		$this->mailer = $mailer;
		$this->formFactoryFactory = $formFactoryFactory;
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
		$form = $this->loginFormFactory->create();

		foreach ($this->securityManager->getLoginProviders() as $loginProvider) {
			$form['socialButtons'][str_replace(' ', '_', $loginProvider)]->onClick[] = function () use ($loginProvider) {
				$this->redirect('login!', $loginProvider);
			};
		}

		$form->onSuccess[] = $this->formSuccess;
		return $form;
	}


	protected function createComponentProviderForm()
	{
		$this->providerFormFactory->setProvider($this->provider);

		$form = $this->providerFormFactory->invoke();
		$form['cancel']->onClick[] = function () {
			$this->redirect('this', array('provider' => NULL));
		};
		$form->onSuccess[] = $this->providerFormSuccess;
		return $form;
	}


	protected function createComponentResetForm()
	{
		$form = $this->resetFormFactory->create();
		$form['cancel']->onClick[] = function () {
			$this->redirect('this', array('reset' => NULL));
		};
		$form->onSuccess[] = $this->resetFormSuccess;
		return $form;
	}


	protected function createComponentConfirmForm()
	{
		if (($userEntity = $this->userDao->findOneBy(array('resetKey' => $this->key))) === NULL) {
			throw new BadRequestException;
		}

		$form = $this->formFactoryFactory
			->create($this->confirmFormFactory)
			->setEntity($userEntity)
			->create();

		$form->onSuccess[] = $this->confirmFormSuccess;
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

		if ($button === $form['_submit']) {
			try {
				$form->presenter->user->login($values->username, $values->password);
			} catch (AuthenticationException $e) {
				$this->onError($this, $e->getMessage());
			}

		} else {
			$this->redirect('login!', str_replace('_', ' ', $button->name));
		}

		$this->onSuccess($this);
		$this->redirect('this');
	}


	public function providerFormSuccess(Form $form)
	{
		$this->redirect('login', array($form['provider']->value, json_encode((array)$form['parameters']->values)));
	}


	public function resetFormSuccess(Form $form)
	{
		/** @var UserEntity $user */
		$user = $this->userDao->findOneBy(array('email' => $form['email']->value));

		if (!$user) {
			$this->flashMessage($this->translator->translate('User with email %email% does not exist.', NULL, array(
				'email' => $form['email']->value,
			)), 'warning');
			return;
		}

		$this->sendEmail($user, $user->resetPassword());
		$this->userDao->save($user);

		$this->flashMessage($this->translator->translate('New password has been sended'), 'success');
		$this->redirect('this', array('reset' => NULL));
	}


	public function confirmFormSuccess()
	{
		if (($userEntity = $this->userDao->findOneBy(array('resetKey' => $this->key))) === NULL) {
			throw new BadRequestException;
		}

		$this->securityManager->sendChangedPassword($userEntity);

		$this->flashMessage($this->translator->translate('New password has been saved'), 'success');
		$this->redirect('this', array('key' => NULL));
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
			$this->onError($this, $e->getMessage());
		}

		$this->redirect('this');
	}


	private function sendEmail(UserEntity $user, $key)
	{
		$absoluteUrls = $this->presenter->absoluteUrls;
		$this->presenter->absoluteUrls = true;
		$link = $this->link('this', array('key' => $key, 'reset' => NULL));
		$this->presenter->absoluteUrls = $absoluteUrls;

		$this->securityManager->sendRecoveryUrl($user, $link);
	}

}
