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
use Nette\Application\BadRequestException;
use Nette\Application\UI\Form;
use Nette\Mail\IMailer;
use Nette\Mail\Message;
use Venne\Bridges\Kdyby\DoctrineForms\FormFactoryFactory;
use Venne\Security\AuthorizatorFactory;
use Venne\Security\User\ExtendedUser;
use Venne\Security\Role\Role;
use Venne\Security\SecurityManager;
use Venne\Security\User\User;
use Venne\System\Invitation\Invitation;
use Venne\System\Registration\LoginProviderMode;
use Venne\System\Registration\RegistrationMode;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 *
 * @method onSuccess(\Venne\Security\Registration\RegistrationControl $control)
 * @method onEnable(\Venne\Security\Registration\RegistrationControl $control)
 */
class RegistrationControl extends \Venne\System\UI\Control
{

	/** @var int */
	private $key;

	/** @var callable[] */
	public $onSuccess;

	/** @var callable[] */
	public $onEnable;

	/** @var callable[] */
	public $onError;

	/** @var callable[] */
	public $onLoad;

	/** @var \Venne\System\Invitation\Invitation */
	private $invitation;

	/** @var string */
	private $userType;

	/** @var \Venne\System\Registration\RegistrationMode */
	private $mode;

	/** @var \Venne\System\Registration\LoginProviderMode */
	private $loginProviderMode;

	/** @var string|array */
	private $roles;

	/** @var \Doctrine\ORM\EntityManager */
	private $entityManager;

	/** @var \Venne\Security\SecurityManager */
	private $securityManager;

	/** @var \Venne\Security\AuthorizatorFactory */
	private $authorizatorFactory;

	/** @var \Kdyby\Doctrine\EntityRepository */
	private $roleRepository;

	/** @var \Nette\Mail\IMailer */
	private $mailer;

	/** @var \Venne\Bridges\Kdyby\DoctrineForms\FormFactoryFactory */
	private $formFactoryFactory;

	/** @var \Venne\Security\User\ExtendedUser */
	private $currentUser;

	/** @var string */
	private $defaultEmail;

	/**
	 * @param string $userType
	 * @param \Venne\System\Registration\RegistrationMode $mode
	 * @param \Venne\System\Registration\LoginProviderMode $loginProviderMode
	 * @param string[] $roles
	 */
	public function __construct($userType, RegistrationMode $mode, LoginProviderMode $loginProviderMode, $roles)
	{
		parent::__construct();

		$this->loginProviderMode = $loginProviderMode;
		$this->mode = $mode;
		$this->roles = $roles;
		$this->userType = $userType;
	}

	/**
	 * @param \Venne\System\Invitation\Invitation $invitation
	 */
	public function setInvitation(Invitation $invitation)
	{
		$this->invitation = $invitation;
	}

	public function inject(
		EntityManager $entityManager,
		SecurityManager $securityManager,
		AuthorizatorFactory $authorizatorFactory,
		IMailer $mailer,
		FormFactoryFactory $formFactoryFactory
	) {
		$this->entityManager = $entityManager;
		$this->roleRepository = $entityManager->getRepository(Role::class);
		$this->securityManager = $securityManager;
		$this->authorizatorFactory = $authorizatorFactory;
		$this->entityManager = $entityManager;
		$this->mailer = $mailer;
		$this->formFactoryFactory = $formFactoryFactory;
	}

	/**
	 * @param string $name
	 */
	public function handleLoad($name)
	{
//		/** @var $loginProvider \Venne\Security\ILoginProvider */
//		$loginProvider = $this->securityManager->getLoginProviderByName($name);
//
//		$identity = null;
//
//		try {
//			$identity = $loginProvider->authenticate(array());
//		} catch (AuthenticationException $e) {
//		}
//
//		if ($identity) {
//			$this->onError($this, 'The user is already registered');
//		}
//
//		$this->authorizatorFactory->clearPermissionSession();
//
//		$formFactory = $this->securityManager->getUserTypeByClass($this->userType)->getRegistrationFormFactory();
//
//		if (!$formFactory instanceof IRegistrationFormFactory) {
//			throw new InvalidArgumentException("Form factory '" . get_class($formFactory) . "' is not istance of \Venne\System\Content\IRegistrationFormFactory");
//		}
//
//		$formFactory->connectWithLoginProvider($this['form'], $loginProvider);
//
//		/** @var $form \Venne\Forms\Form */
//		$form = $this['form'];
//		$form->onSuccess = null;
//
//		if ($this->loginProviderMode->equalsValue(LoginProviderMode::LOAD_AND_SAVE)) {
//			$form->setSubmittedBy($form->getSaveButton());
//			$form->fireEvents();
//
//			if ($form->isValid()) {
//				$loginProvider->connectWithUser($form->getData()->user);
//
//				$identity = $loginProvider->authenticate(array());
//				if ($identity) {
//					$this->presenter->getUser()->login($identity);
//					$this->redirect('this');
//				}
//			}
//		} elseif ($this->loginProviderMode->equalsValue(LoginProviderMode::LOAD)) {
//			$this->onLoad($this);
//		}
	}

	public function renderDefault()
	{
		if ($this->key) {
			$this->enable();
			$this->onEnable($this);
		}
	}

	/**
	 * @return \Nette\Application\UI\Form
	 */
	public function createComponentForm()
	{
		$userType = $this->securityManager->getUserTypeByClass($this->userType);

		$userType->getRegistrationFormService()->getFormFactory();

		$form = $userType
			->getRegistrationFormService()
			->getFormFactory()
			->create();

		if ($this->invitation !== null) {
			$form['user']['email']->setDefaultValue($this->invitation->getEmail());
		}

		foreach ($this->securityManager->getLoginProviders() as $loginProvider) {
			$submit = $form->addSubmit('_submit_' . str_replace(' ', '_', $loginProvider), $loginProvider);
			$submit->setValidationScope(false);
			$submit->onClick[] = function () use ($loginProvider) {
				$this->redirect('load!', array($loginProvider));
			};
		}

		$form->onSuccess[] = function (Form $form) {
			if ($this->mode->equalsValue(RegistrationMode::BASIC)) {
				$this->currentUser = $this->createNewUser();
				$this->presenter->getUser()->login($this->currentUser->getUser()->getEmail(), $form['user']['password']->getValue());
			}

			if ($this->mode->equalsValue(RegistrationMode::MAIL) || $this->mode->equalsValue(RegistrationMode::MAIL_CHECKUP)) {
				$this->sendEmail($form['user']->getValue());
			}

			$this->onSuccess($this);
		};

		return $form;
	}

	private function sendEmail($user)
	{
		$absoluteUrls = $this->presenter->absoluteUrls;
		$this->presenter->absoluteUrls = true;
		$link = $this->link('this', array('key' => $user->user->key));
		$this->presenter->absoluteUrls = $absoluteUrls;

		$text = $this->emailText;
		$text = strtr($text, array(
			'{$email}' => $user->user->email,
			'{$link}' => sprintf('<a href="%s">%s</a>', $link, $link),
		));

		$mail = new Message();
		$mail->setFrom(sprintf('%s <%s>', $this->emailSender, $this->emailFrom))
			->addTo($user->user->email)
			->setSubject($this->emailSubject)
			->setHTMLBody($text);
		$this->mailer->send($mail);
	}

	/**
	 * @return \Venne\Security\User\ExtendedUser
	 */
	protected function createNewUser()
	{
		/** @var \Kdyby\Doctrine\EntityRepository $repository */
		$repository = $this->entityManager->getRepository($this->userType);
		$class = $repository->getClassName();

		/** @var $entity ExtendedUser */
		$entity = new $class;
		if ($this->mode->equalsValue(RegistrationMode::CHECKUP)) {
			$entity->getUser()->setPublished(false);

		} elseif ($this->mode->equalsValue(RegistrationMode::MAIL)) {
			$entity->getUser()->disableByKey();

		} elseif ($this->mode->equalsValue(RegistrationMode::MAIL_CHECKUP)) {
			$entity->getUser()->disableByKey();
			$entity->getUser()->setPublished(false);
		}

		foreach ((array) $this->roles as $role) {
			$entity->getUser()->addRoleEntity($role);
		}

		if ($this->defaultEmail) {
			$entity->getUser()->setEmail($this->defaultEmail);
		}

		return $entity;
	}

	protected function enable()
	{
		if ($this->mode->equalsValue(RegistrationMode::MAIL) || $this->mode->equalsValue(RegistrationMode::MAIL_CHECKUP)) {
			$repository = $this->entityManager->getRepository(User::class);
			$user = $repository->findOneByKey($this->key);
			if (!$user) {
				throw new BadRequestException;
			} else {
				$user->enableByKey($this->key);
				$this->entityManager->flush($user);
			}
		}
	}

	public function loadState(array $params)
	{
		parent::loadState($params);

		$this->key = isset($params['key']) ? $params['key'] : null;
	}

	public function saveState(array & $params, $reflection = null)
	{
		parent::saveState($params, $reflection);

		$params['key'] = $this->key;
	}

}
