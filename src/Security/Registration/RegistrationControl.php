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

use Doctrine\ORM\EntityManager;
use Kdyby\Doctrine\EntityDao;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Form;
use Nette\InvalidArgumentException;
use Nette\Mail\IMailer;
use Nette\Security\AuthenticationException;
use Venne\Bridges\Kdyby\DoctrineForms\FormFactoryFactory;
use Venne\Security\AuthorizatorFactory;
use Venne\Security\ExtendedUserEntity;
use Venne\Security\SecurityManager;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class RegistrationControl extends \Venne\System\UI\Control
{

	/**
	 * @var int
	 *
	 * @persistent
	 */
	public $key;

	/** @var callable[] */
	public $onSuccess;

	/** @var callable[] */
	public $onEnable;

	/** @var callable[] */
	public $onError;

	/** @var callable[] */
	public $onLoad;

	/** @var bool */
	private $invitaions;

	/** @var string */
	private $userType;

	/** @var string */
	private $mode;

	/** @var string */
	private $loginProviderMode;

	/** @var string|array */
	private $roles;

	/** @var \Venne\Security\SecurityManager */
	private $securityManager;

	/** @var \Venne\Security\AuthorizatorFactory */
	private $authorizatorFactory;

	/** @var \Kdyby\Doctrine\EntityManager */
	private $entityManager;

	/** @var \Kdyby\Doctrine\EntityDao */
	private $roleDao;

	/** @var \Nette\Mail\IMailer */
	private $mailer;

	/** @var \Venne\Bridges\Kdyby\DoctrineForms\FormFactoryFactory */
	private $formFactoryFactory;

	/** @var \Venne\Security\ExtendedUserEntity */
	private $_currentUser;

	/** @var string */
	private $defaultEmail;

	/**
	 * @param \Kdyby\Doctrine\EntityDao $roleDao
	 * @param boolean $invitaions
	 * @param string $userType
	 * @param string $mode
	 * @param string $loginProviderMode
	 * @param string[] $roles
	 */
	public function __construct(EntityDao $roleDao, $invitaions, $userType, $mode, $loginProviderMode, $roles)
	{
		parent::__construct();

		$this->roleDao = $roleDao;
		$this->invitaions = $invitaions;
		$this->loginProviderMode = $loginProviderMode;
		$this->mode = $mode;
		$this->roles = $roles;
		$this->userType = $userType;
	}

	public function inject(
		SecurityManager $securityManager,
		AuthorizatorFactory $authorizatorFactory,
		EntityManager $entityManager,
		IMailer $mailer,
		FormFactoryFactory $formFactoryFactory
	)
	{
		$this->securityManager = $securityManager;
		$this->authorizatorFactory = $authorizatorFactory;
		$this->entityManager = $entityManager;
		$this->mailer = $mailer;
		$this->formFactoryFactory = $formFactoryFactory;
	}

	/**
	 * @param string $defaultEmail
	 */
	public function setDefaultEmail($defaultEmail)
	{
		$this->defaultEmail = $defaultEmail;
	}

	/**
	 * @param string $name
	 */
	public function handleLoad($name)
	{
		/** @var $loginProvider \Venne\Security\ILoginProvider */
		$loginProvider = $this->securityManager->getLoginProviderByName($name);

		$identity = null;

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
		$form->onSuccess = null;

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

	/**
	 * @return \Nette\Application\UI\Form
	 */
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
				->setValidationScope(false)
				->onClick[] = function () use ($loginProvider) {
				$this->redirect('load!', array($loginProvider));
			};
		}

		$form->onSuccess[] = $this->formSuccess;

		return $form;
	}

	public function formSuccess(Form $form)
	{
		if ($this->mode === 'basic') {
			$this->presenter->user->login($this->_currentUser->user->email, $form['user']['password']->value);
		}

		if ($this->mode === 'mail' || $this->mode === 'mail&checkup') {
			$this->sendEmail($form);
		}

		$this->onSuccess($this);
	}

	/**
	 * @param $form
	 */
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
	 * @return \Venne\Security\ExtendedUserEntity
	 */
	protected function createNewUser()
	{
		/** @var \Kdyby\Doctrine\EntityDao $dao */
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
		foreach ((array) $this->roles as $role) {
			$entity->getUser()->addRoleEntitie($role);
		}

		if ($this->defaultEmail) {
			$entity->user->email = $this->defaultEmail;
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
