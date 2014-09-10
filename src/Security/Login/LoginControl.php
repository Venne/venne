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

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class LoginControl extends \Venne\System\UI\Control
{

	/** @var callable[] */
	public $onSuccess;

	/** @var callable[] */
	public $onError;

	/**
	 * @var string
	 *
	 * @persistent
	 */
	public $provider;

	/**
	 * @var bool
	 *
	 * @persistent
	 */
	public $reset;

	/**
	 * @var int
	 *
	 * @persistent
	 */
	public $key;

	/** @var \Venne\System\AdminModule\LoginFormFactory */
	private $loginFormFactory;

	/** @var \Venne\Security\AdminModule\ProviderFormFactory */
	private $providerFormFactory;

	/** @var \Venne\Security\Login\ResetFormFactory */
	private $resetFormFactory;

	/** @var \Venne\Security\Login\ConfirmFormFactory */
	private $confirmFormFactory;

	/** @var \Venne\Security\SecurityManager */
	private $securityManager;

	/** @var \Kdyby\Doctrine\EntityDao */
	private $userDao;

	/** @var \Nette\Mail\IMailer */
	private $mailer;

	/** @var \Venne\Bridges\Kdyby\DoctrineForms\FormFactoryFactory */
	private $formFactoryFactory;

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
	 * @return \Venne\Security\SecurityManager
	 */
	public function getSecurityManager()
	{
		return $this->securityManager;
	}

	/**
	 * @return \Nette\Forms\Form
	 */
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

	/**
	 * @return \Nette\Application\UI\Form
	 */
	protected function createComponentProviderForm()
	{
		$this->providerFormFactory->setProvider($this->provider);

		$form = $this->providerFormFactory->invoke();
		$form['cancel']->onClick[] = function () {
			$this->redirect('this', array('provider' => null));
		};
		$form->onSuccess[] = $this->providerFormSuccess;

		return $form;
	}

	/**
	 * @return \Nette\Application\UI\Form
	 */
	protected function createComponentResetForm()
	{
		$form = $this->resetFormFactory->create();
		$form['cancel']->onClick[] = function () {
			$this->redirect('this', array('reset' => null));
		};
		$form->onSuccess[] = $this->resetFormSuccess;

		return $form;
	}

	/**
	 * @return \Nette\Application\UI\Form
	 */
	protected function createComponentConfirmForm()
	{
		if (($userEntity = $this->userDao->findOneBy(array('resetKey' => $this->key))) === null) {
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
			$form->presenter->user->setExpiration('+ 14 days', false);
		} else {
			$form->presenter->user->setExpiration('+ 20 minutes', true);
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
		$this->redirect('login', array($form['provider']->value, json_encode((array) $form['parameters']->values)));
	}

	public function resetFormSuccess(Form $form)
	{
		/** @var \Venne\Security\UserEntity $user */
		$user = $this->userDao->findOneBy(array('email' => $form['email']->value));

		if (!$user) {
			$this->flashMessage($this->translator->translate('User with email %email% does not exist.', null, array(
				'email' => $form['email']->value,
			)), 'warning');

			return;
		}

		$this->sendEmail($user, $user->resetPassword());
		$this->userDao->save($user);

		$this->flashMessage($this->translator->translate('New password has been sended'), 'success');
		$this->redirect('this', array(
			'reset' => null
		));
	}

	public function confirmFormSuccess()
	{
		if (($userEntity = $this->userDao->findOneBy(array('resetKey' => $this->key))) === null) {
			throw new BadRequestException;
		}

		$this->securityManager->sendNewPassword($userEntity);

		$this->flashMessage($this->translator->translate('New password has been saved'), 'success');
		$this->redirect('this', array(
			'key' => null
		));
	}

	/**
	 * @param string $name
	 * @param mixed|null $parameters
	 */
	public function handleLogin($name, $parameters = null)
	{
		$login = $this->securityManager->getLoginProviderByName($name);

		if (($container = $login->getFormContainer()) !== null && $parameters == null) {
			$this->redirect('this', array('provider' => $name));

		} else {
			if ($parameters) {
				$parameters = json_decode($parameters, true);
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

	/**
	 * @param string $provider
	 * @param mixed|null $parameters
	 */
	private function authenticate($provider, $parameters = null)
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

	/**
	 * @param \Venne\Security\UserEntity $user
	 * @param string $key
	 */
	private function sendEmail(UserEntity $user, $key)
	{
		$absoluteUrls = $this->presenter->absoluteUrls;
		$this->presenter->absoluteUrls = true;
		$link = $this->link('this', array('key' => $key, 'reset' => null));
		$this->presenter->absoluteUrls = $absoluteUrls;

		$this->securityManager->sendRecoveryUrl($user, $link);
	}

}
