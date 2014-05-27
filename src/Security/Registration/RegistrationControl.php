<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Security\Registration;

use Kdyby\Doctrine\EntityDao;
use Venne\Bridges\Kdyby\DoctrineForms\FormFactoryFactory;
use Venne\Security\ExtendedUserEntity;
use Venne\System\Content\IRegistrationFormFactory;
use Venne\Security\AuthorizatorFactory;
use Venne\Security\SecurityManager;
use Doctrine\ORM\EntityManager;
use Nette\InvalidArgumentException;
use Nette\Mail\IMailer;
use Nette\Security\AuthenticationException;
use Venne\System\UI\Control;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class RegistrationControl extends Control
{

	/** @persistent */
	public $key;

	/** @var array */
	public $onSuccess;

	/** @var array */
	public $onEnable;

	/** @var array */
	public $onError;

	/** @var array */
	public $onLoad;

	/** @var bool */
	private $byRequest;

	/** @var string */
	private $userType;

	/** @var string */
	private $mode;

	/** @var string */
	private $loginProviderMode;

	/** @var string|array */
	private $roles;

	/** @var string */
	private $emailSubject;

	/** @var string */
	private $emailFrom;

	/** @var string */
	private $emailSender;

	/** @var string */
	private $emailText;

	/** @var SecurityManager */
	private $securityManager;

	/** @var AuthorizatorFactory */
	private $authorizatorFactory;

	/** @var EntityManager */
	private $entityManager;

	/** @var EntityDao */
	private $roleDao;

	/** @var IMailer */
	private $mailer;

	/** @var FormFactoryFactory */
	private $formFactoryFactory;

	/** @var ExtendedUserEntity */
	private $_currentUser;


	public function __construct(EntityDao $roleDao, $byRequest, $userType, $mode, $loginProviderMode, $roles, $emailSender, $emailFrom, $emailSubject, $emailText)
	{
		parent::__construct();

		$this->roleDao = $roleDao;
		$this->byRequest = $byRequest;
		$this->loginProviderMode = $loginProviderMode;
		$this->mode = $mode;
		$this->roles = $roles;
		$this->userType = $userType;

		$this->emailSender = $emailSender;
		$this->emailFrom = $emailFrom;
		$this->emailSubject = $emailSubject;
		$this->emailText = $emailText;
	}


	/**
	 * @param SecurityManager $securityManager
	 * @param AuthorizatorFactory $authorizatorFactory
	 * @param EntityManager $entityManager
	 * @param IMailer $mailer
	 * @param FormFactoryFactory $formFactoryFactory
	 */
	public function inject(
		SecurityManager $securityManager,
		AuthorizatorFactory $authorizatorFactory,
		EntityManager $entityManager,
		IMailer $mailer,
		FormFactoryFactory $formFactoryFactory
	){
		$this->securityManager = $securityManager;
		$this->authorizatorFactory = $authorizatorFactory;
		$this->entityManager = $entityManager;
		$this->mailer = $mailer;
		$this->formFactoryFactory = $formFactoryFactory;
	}

	public function handleLoad($name)
	{
		/** @var $loginProvider \Venne\Security\ILoginProvider */
		$loginProvider = $this->securityManager->getLoginProviderByName($name);

		$identity = NULL;

		try {
			$identity = $loginProvider->authenticate(array());
		} catch (AuthenticationException $e) {
		}

		if ($identity) {
			$this->onError($this, 'The user is already registered');
		}

		$this->authorizatorFactory->clearPermissionSession();

		$formFactory = $this->securityManager->getUserTypeByClass($this->userType)->getRegistrationFormFactory();

		if (!$formFactory instanceof IRegistrationFormFactory) {
			throw new InvalidArgumentException("Form factory '" . get_class($formFactory) . "' is not istance of \Venne\System\Content\IRegistrationFormFactory");
		}

		$formFactory->connectWithLoginProvider($this['form'], $loginProvider);

		/** @var $form \Venne\Forms\Form */
		$form = $this['form'];
		$form->onSuccess = NULL;

		if ($this->loginProviderMode === 'load&save') {
			$form->setSubmittedBy($form->getSaveButton());
			$form->fireEvents();

			if ($form->isValid()) {
				$loginProvider->connectWithUser($form->getData()->user);

				$identity = $loginProvider->authenticate(array());
				if ($identity) {
					$this->presenter->user->login($identity);
					$this->redirect('this');
				}
			}
		} else if ($this->loginProviderMode === 'load') {
			$this->onLoad($this);
		}
	}


	public function render()
	{
		if ($this->key) {
			$this->enable();
			$this->onEnable($this);
		}

		call_user_func_array(array($this, 'parent::render'), func_get_args());
	}


	public function createComponentForm()
	{
		$userType = $this->securityManager->getUserTypeByClass($this->userType);
		$this->_currentUser = $this->createNewUser();
		$form = $this->formFactoryFactory
			->create($userType->getRegistrationFormFactory())
			->setEntity($this->_currentUser)
			->create();

		foreach ($this->securityManager->getLoginProviders() as $loginProvider) {
			$form->addSubmit('_submit_' . str_replace(' ', '_', $loginProvider), $loginProvider)
				->setValidationScope(FALSE)
				->onClick[] = function () use ($loginProvider) {
				$this->redirect('load!', array($loginProvider));
			};
		}

		$form->onSuccess[] = $this->formSuccess;
		return $form;
	}


	public function formSuccess($form)
	{
		if ($this->mode === 'basic') {
			$this->presenter->user->login($this->_currentUser->user->email, $form['user']['password']->value);
		}

		if ($this->mode === 'mail' || $this->mode === 'mail&checkup') {
			$this->sendEmail($form);
		}

		$this->onSuccess($this);
	}


	private function sendEmail($form)
	{
		$user = $form->data;
		$absoluteUrls = $this->presenter->absoluteUrls;
		$this->presenter->absoluteUrls = true;
		$link = $this->link('this', array('key' => $user->user->key));
		$this->presenter->absoluteUrls = $absoluteUrls;

		$text = $this->emailText;
		$text = strtr($text, array(
			'{$email}' => $user->user->email,
			'{$password}' => $form['user']['password']->value,
			'{$link}' => '<a href="' . $link . '">' . $link . '</a>'
		));

		$mail = $this->presenter->context->nette->createMail();
		$mail->setFrom("{$this->emailSender} <{$this->emailFrom}>")
			->addTo($user->user->email)
			->setSubject($this->emailSubject)
			->setHTMLBody($text);
		$this->mailer->send($mail);
	}


	/**
	 * @return ExtendedUserEntity
	 */
	protected function createNewUser()
	{
		/** @var EntityDao $dao */
		$dao = $this->entityManager->getDao($this->userType);
		$class = $dao->getClassName();

		/** @var $entity ExtendedUserEntity */
		$entity = new $class;
		if ($this->mode === 'checkup') {
			$entity->getUser()->setPublished(false);
		} elseif ($this->mode === 'mail') {
			$entity->getUser()->disableByKey();
		} elseif ($this->mode === 'mail&checkup') {
			$entity->getUser()->disableByKey();
			$entity->getUser()->setPublished(false);
		}
		foreach ((array)$this->roles as $role) {
			$entity->getUser()->addRoleEntitie($this->roleDao->findOneBy(array('name' => $role)));
		}

		return $entity;
	}


	protected function enable()
	{
		if ($this->mode === 'mail' || $this->mode === 'mail&checkup') {
			$repository = $this->entityManager->getRepository('Venne\System\Pages\Users\UserEntity');
			$user = $repository->findOneByKey($this->key);
			if (!$user) {
				throw new BadRequestException;
			} else {
				$user->enableByKey($this->key);
				$repository->save($user);
			}
		}
	}

}
