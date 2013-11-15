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
use CmsModule\Forms\ConfirmFormFactory;
use CmsModule\Forms\LoginFormFactory;
use CmsModule\Forms\ProviderFormFactory;
use CmsModule\Forms\ResetFormFactory;
use CmsModule\Pages\Users\UserEntity;
use CmsModule\Security\Repositories\UserRepository;
use CmsModule\Security\SecurityManager;
use Nette\Application\BadRequestException;
use Nette\Security\AuthenticationException;
use Venne\Forms\Form;

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
	protected $loginFormFactory;

	/** @var ProviderFormFactory */
	protected $providerFormFactory;

	/** @var ResetFormFactory */
	protected $resetFormFactory;

	/** @var ConfirmFormFactory */
	protected $confirmFormFactory;

	/** @var SecurityManager */
	protected $securityManager;

	/** @var UserRepository */
	protected $userRepository;

	/** @var string */
	protected $emailSubject;

	/** @var string */
	protected $emailText;

	/** @var string */
	protected $emailSender;

	/** @var string */
	protected $emailFrom;


	/**
	 * @param LoginFormFactory $loginFormFactory
	 * @param ProviderFormFactory $providerFormFactory
	 * @param ResetFormFactory $resetFormFactory
	 * @param ConfirmFormFactory $confirmFormFactory
	 * @param SecurityManager $securityManager
	 * @param UserRepository $userRepository
	 */
	public function __construct(
		LoginFormFactory $loginFormFactory,
		ProviderFormFactory $providerFormFactory,
		ResetFormFactory $resetFormFactory,
		ConfirmFormFactory $confirmFormFactory,
		SecurityManager $securityManager,
		UserRepository $userRepository
	)
	{
		parent::__construct();

		$this->loginFormFactory = $loginFormFactory;
		$this->providerFormFactory = $providerFormFactory;
		$this->resetFormFactory = $resetFormFactory;
		$this->confirmFormFactory = $confirmFormFactory;
		$this->securityManager = $securityManager;
		$this->userRepository = $userRepository;
	}


	/**
	 * @param $emailSubject
	 * @param $emailText
	 * @param $emailSender
	 * @param $emailFrom
	 */
	public function setResetEmail($emailSubject, $emailText, $emailSender, $emailFrom)
	{
		$this->emailSubject = $emailSubject;
		$this->emailText = $emailText;
		$this->emailSender = $emailSender;
		$this->emailFrom = $emailFrom;
	}


	public function startup()
	{
		parent::startup();

		if ($this->reset && !$this->emailFrom) {
			throw new BadRequestException;
		}

		if ($this->emailFrom) {
			$this->template->forgotPassword = TRUE;
		}
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


	protected function createComponentResetForm()
	{
		$_this = $this;
		$form = $this->resetFormFactory->invoke();
		$form['cancel']->onClick[] = function () use ($_this) {
			$_this->redirect('this', array('reset' => NULL));
		};
		$form->onSuccess[] = $this->resetFormSuccess;
		$form->onError[] = function ($form) {
			dump($form->errors);
			die();
		};
		return $form;
	}


	protected function createComponentConfirmForm()
	{
		if (($userEntity = $this->userRepository->findOneBy(array('resetKey' => $this->key))) === NULL) {
			throw new BadRequestException;
		}

		$form = $this->confirmFormFactory->invoke($userEntity);
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

		if ($button === $form->getSaveButton()) {
			try {
				$form->presenter->user->login($values->username, $values->password);
			} catch (AuthenticationException $e) {
				$this->onError($this, $e->getMessage());
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


	public function resetFormSuccess(Form $form)
	{
		/** @var UserEntity $user */
		$user = $this->userRepository->findOneBy(array('email' => $form['email']->value));

		if (!$user) {
			$this->flashMessage($this->translator->translate('User with email %email% does not exist.', NULL, array(
				'email' => $form['email']->value,
			)), 'warning');
			return;
		}

		$this->sendEmail($user, $user->resetPassword());
		$this->userRepository->save($user);

		$this->flashMessage($this->translator->translate('New password has been sended'), 'success');
		$this->redirect('this', array('reset' => NULL));
	}


	public function confirmFormSuccess(Form $form)
	{
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

		$text = $this->emailText;
		$text = strtr($text, array(
			'{$email}' => $user->email,
			'{$link}' => '<a href="' . $link . '">' . $link . '</a>'
		));

		$mail = $this->presenter->context->nette->createMail();
		$mail->setFrom($this->emailFrom, $this->emailSender)
			->addTo($user->email)
			->setSubject($this->emailSubject)
			->setHTMLBody($text);
		dump($mail);
		$mail->send();
	}

}
