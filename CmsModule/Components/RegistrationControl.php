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
use CmsModule\Content\IRegistrationFormFactory;
use CmsModule\Pages\Users\ExtendedUserEntity;
use CmsModule\Security\AuthorizatorFactory;
use CmsModule\Security\Repositories\RoleRepository;
use CmsModule\Security\SecurityManager;
use Doctrine\ORM\EntityManager;
use Nette\InvalidArgumentException;
use Nette\Security\AuthenticationException;

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

	/** @var string */
	protected $userType;

	/** @var string */
	protected $mode;

	/** @var string */
	protected $loginProviderMode;

	/** @var string|array */
	protected $roles;

	/** @var string */
	protected $emailSubject;

	/** @var string */
	protected $emailFrom;

	/** @var string */
	protected $emailSender;

	/** @var string */
	protected $emailText;

	/** @var SecurityManager */
	protected $securityManager;

	/** @var AuthorizatorFactory */
	protected $authorizatorFactory;

	/** @var EntityManager */
	protected $entityManager;

	/** @var RoleRepository */
	protected $roleRepository;


	/**
	 * @param string $userType
	 * @param string $mode
	 * @param string $loginProviderMode
	 * @param string|array $roles
	 * @param string $emailSender
	 * @param string $emailFrom
	 * @param string $emailSubject
	 * @param string $emailText
	 */
	public function __construct($userType, $mode, $loginProviderMode, $roles, $emailSender, $emailFrom, $emailSubject, $emailText)
	{
		parent::__construct();

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
	 */
	public function injectSecurityManager(SecurityManager $securityManager)
	{
		$this->securityManager = $securityManager;
	}


	/**
	 * @param AuthorizatorFactory $authorizatorFactory
	 */
	public function injectAuthorizatorFactory(AuthorizatorFactory $authorizatorFactory)
	{
		$this->authorizatorFactory = $authorizatorFactory;
	}


	/**
	 * @param \Doctrine\ORM\EntityManager $entityManager
	 */
	public function injectEntityManager(EntityManager $entityManager)
	{
		$this->entityManager = $entityManager;
	}


	/**
	 * @param \CmsModule\Security\Repositories\RoleRepository $roleRepository
	 */
	public function injectRoleRepository(RoleRepository $roleRepository)
	{
		$this->roleRepository = $roleRepository;
	}

	public function handleLoad($name)
	{
		/** @var $loginProvider \CmsModule\Security\ILoginProvider */
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
			throw new InvalidArgumentException("Form factory '" . get_class($formFactory) . "' is not istance of \CmsModule\Content\IRegistrationFormFactory");
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
		$_this = $this;
		$userType = $this->securityManager->getUserTypeByClass($this->userType);

		$form = $userType->getRegistrationFormFactory()->invoke($this->createNewUser());

		foreach ($this->securityManager->getLoginProviders() as $loginProvider) {
			$form->addSubmit('_submit_' . str_replace(' ', '_', $loginProvider), $loginProvider)
				->setValidationScope(FALSE)
				->onClick[] = function () use ($_this, $loginProvider) {
				$_this->redirect('load!', array($loginProvider));
			};
		}

		$form->onSuccess[] = $this->formSuccess;
		return $form;
	}


	public function formSuccess($form)
	{
		if ($this->mode === 'basic') {
			$this->presenter->user->login($form->getData()->user->email, $form['user']['password']->value);
		}

		if ($this->mode === 'mail' || $this->mode === 'mail&checkup') {
			$this->sendEmail($form);
		}

		$this->onSuccess($this);
	}


	public function sendEmail($form)
	{
		$user = $form->data;
		$absoluteUrls = $this->absoluteUrls;
		$this->presenter->absoluteUrls = true;
		$link = $this->link('this', array('key' => $user->user->key));
		$this->presenter->absoluteUrls = $absoluteUrls;

		$text = $this->emailText;
		$text = strtr($text, array(
			'{$email}' => $user->user->email,
			'{$password}' => $form['user']['password']->value,
			'{$link}' => '<a href="' . $link . '">' . $link . '</a>'
		));

		$mail = $this->context->nette->createMail();
		$mail->setFrom("{$this->emailSender} <{$this->emailFrom}>")
			->addTo($user->user->email)
			->setSubject($this->emailSubject)
			->setHTMLBody($text)
			->send();
	}


	/**
	 * @return ExtendedUserEntity
	 */
	protected function createNewUser()
	{
		$repository = $this->entityManager->getRepository($this->userType);

		/** @var $entity ExtendedUserEntity */
		$entity = $repository->createNew();
		if ($this->mode === 'checkup') {
			$entity->getUser()->setPublished(false);
		} elseif ($this->mode === 'mail') {
			$entity->getUser()->disableByKey();
		} elseif ($this->mode === 'mail&checkup') {
			$entity->getUser()->disableByKey();
			$entity->getUser()->setPublished(false);
		}
		foreach ((array)$this->roles as $role) {
			$entity->getUser()->roleEntities[] = $this->roleRepository->findOneBy(array('name' => $role));
		}

		return $entity;
	}


	protected function enable()
	{
		if ($this->mode === 'mail' || $this->mode === 'mail&checkup') {
			$repository = $this->entityManager->getRepository('CmsModule\Pages\Users\UserEntity');
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
