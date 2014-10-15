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

use Doctrine\ORM\EntityManager;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Form;
use Nette\Mail\IMailer;
use Nette\Security\AuthenticationException;
use Venne\Bridges\Kdyby\DoctrineForms\FormFactoryFactory;
use Venne\Security\AdminModule\ProviderFormFactory;
use Venne\Security\SecurityManager;
use Venne\Security\User;
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

	/** @var \Venne\Security\Login\ResetFormService */
	private $resetFormService;

	/** @var \Venne\Security\Login\ConfirmFormService */
	private $confirmFormService;

	/** @var \Venne\Security\SecurityManager */
	private $securityManager;

	/** @var \Kdyby\Doctrine\EntityRepository */
	private $userRepository;

	/** @var \Venne\Bridges\Kdyby\DoctrineForms\FormFactoryFactory */
	private $formFactoryFactory;

	public function __construct(
		EntityManager $entityManager,
		LoginFormFactory $loginFormFactory,
		ProviderFormFactory $providerFormFactory,
		ResetFormService $resetFormService,
		ConfirmFormService $confirmFormService,
		SecurityManager $securityManager,
		IMailer $mailer,
		FormFactoryFactory $formFactoryFactory
	) {
		parent::__construct();

		$this->userRepository = $entityManager->getRepository(User::class);
		$this->loginFormFactory = $loginFormFactory;
		$this->providerFormFactory = $providerFormFactory;
		$this->resetFormService = $resetFormService;
		$this->confirmFormService = $confirmFormService;
		$this->securityManager = $securityManager;
		$this->formFactoryFactory = $formFactoryFactory;

		$this->redrawControl('content');
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
		$form = $this
			->resetFormService
			->getFormFactory(function ($key) {
				return $this->link('this', array('key' => $key, 'reset' => null));
			})->create();

		$cancel = $form->addSubmit('cancel', 'Cancel');
		$cancel->setValidationScope(false);
		$form->onSuccess[] = function (Form $form) {
			if ($form->isSubmitted() === $form['_submit']) {
				$this->flashMessage($this->translator->translate('New password has been sent.'), 'success');
			}

			$this->redirect('this', array(
				'reset' => null
			));
		};

		return $form;
	}

	/**
	 * @return \Nette\Application\UI\Form
	 */
	protected function createComponentConfirmForm()
	{
		if (($user = $this->userRepository->findOneBy(array('resetKey' => $this->key))) === null) {
			throw new BadRequestException;
		}

		$form = $this->formFactoryFactory
			->create($this->confirmFormService)
			->setEntity($user)
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

	public function confirmFormSuccess()
	{
		if (($user = $this->userRepository->findOneBy(array('resetKey' => $this->key))) === null) {
			throw new BadRequestException;
		}

		$this->securityManager->sendNewPassword($user);

		$this->flashMessage($this->translator->translate('New password has been saved.'), 'success');
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

}
