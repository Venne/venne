<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace CmsModule\Pages\Registration;

use CmsModule\Content\IRegistrationFormFactory;
use CmsModule\Content\Presenters\PagePresenter;
use CmsModule\Content\Repositories\PageRepository;
use CmsModule\Pages\Users\ExtendedUserEntity;
use CmsModule\Security\AuthorizatorFactory;
use CmsModule\Security\SecurityManager;
use Nette\Application\BadRequestException;
use Nette\InvalidArgumentException;
use Nette\Security\AuthenticationException;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class RoutePresenter extends PagePresenter
{

	/** @persistent */
	public $key;

	/** @var SecurityManager */
	protected $securityManager;

	/** @var AuthorizatorFactory */
	protected $authorizatorFactory;

	/** @var PageRepository */
	protected $pageRepository;


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
	 * @param PageRepository $pageRepository
	 */
	public function injectPageRepository(PageRepository $pageRepository)
	{
		$this->pageRepository = $pageRepository;
	}


	protected function startup()
	{
		parent::startup();

		if (!$this->pageRepository->findOneBy(array('special' => 'users'))) {
			$this->template->hideForm = true;
			$this->flashMessage($this->translator->translate('User page does not exist.'), 'warning', false);
		}

		if (!$this->extendedPage->userType) {
			$this->template->hideForm = true;
			$this->flashMessage($this->translator->translate('Userform has not been set'), 'warning', false);
		}

		if ($this->key) {
			$this->setView('confirm');
		}
	}


	/**
	 * @return \Doctrine\ORM\EntityRepository
	 */
	protected function getUserRepository()
	{
		return $this->entityManager->getRepository('\CmsModule\Pages\Users\UserEntity');
	}


	public function handleLoad($name)
	{
		/** @var $socialLogin \CmsModule\Security\ISocialLogin */
		$socialLogin = $this->securityManager->getSocialLoginByName($name);

		try {
			$identity = $socialLogin->authenticate(array());
		} catch (AuthenticationException $e) {
		}

		if ($identity) {
			$this->flashMessage($this->translator->translate('The user is already registered'));
			$this->redirect('this');
		}

		$this->authorizatorFactory->clearPermissionSession();

		$formFactory = $this->securityManager->getUserTypeByClass($this->extendedPage->userType)->getRegistrationFormFactory();

		if (!$formFactory instanceof IRegistrationFormFactory) {
			throw new InvalidArgumentException("Form factory '" . get_class($formFactory) . "' is not istance of \CmsModule\Content\IRegistrationFormFactory");
		}

		$formFactory->setSocialData($this['form'], $socialLogin);

		if (!$socialLogin->getData()) {
			$this->redirectUrl($socialLogin->getLoginUrl());
		}

		/** @var $form \Venne\Forms\Form */
		$form = $this['form'];
		$form->onSuccess = NULL;

		if ($this->extendedPage->getSocialMode() === PageEntity::SOCIAL_MODE_LOAD_AND_SAVE) {
			$form->setSubmittedBy($form->getSaveButton());
			$form->fireEvents();

			if ($form->isValid()) {
				$socialLogin->connectWithUser($form->getData()->user);

				$identity = $socialLogin->authenticate(array());
				if ($identity) {
					$this->user->login($identity);
					$this->redirect('this');
				}
			}
		}
	}


	protected function createComponentForm()
	{
		$userType = $this->securityManager->getUserTypeByClass($this->extendedPage->userType);
		$formFactory = $userType->getRegistrationFormFactory();

		$_this = $this;
		$form = $formFactory->invoke($this->createNewUser());
		$form->onSuccess[] = $this->processSuccess;

		foreach ($this->securityManager->getSocialLogins() as $socialLogin) {
			$form->addSubmit('_submit_' . $socialLogin, $socialLogin)
				->setValidationScope(FALSE)
				->onClick[] = function () use ($_this, $socialLogin) {
				$_this->handleLoad($socialLogin);
			};
		}

		return $form;
	}


	/**
	 * @return ExtendedUserEntity
	 */
	protected function createNewUser()
	{
		$repository = $this->entityManager->getRepository($this->extendedPage->userType);

		/** @var $entity ExtendedUserEntity */
		$entity = $repository->createNew();
		if ($this->extendedPage->mode === PageEntity::MODE_CHECKUP) {
			$entity->getUser()->setPublished(false);
		} elseif ($this->extendedPage->mode === PageEntity::MODE_MAIL) {
			$entity->getUser()->disableByKey();
		} elseif ($this->extendedPage->mode === PageEntity::MODE_MAIL_CHECKUP) {
			$entity->getUser()->disableByKey();
			$entity->getUser()->setPublished(false);
		}
		foreach ($this->extendedPage->roles as $role) {
			$entity->getUser()->roleEntities[] = $role;
		}

		return $entity;
	}


	public function processSuccess($form)
	{
		$this->flashMessage($this->translator->translate('Your registration is complete'), 'success');

		if ($this->extendedPage->mode === PageEntity::MODE_BASIC) {
			$this->user->login($form->getData()->user->email, $form['user']['password']->value);
		}

		// email
		if ($this->extendedPage->mode === PageEntity::MODE_MAIL || $this->extendedPage->mode === PageEntity::MODE_MAIL_CHECKUP) {
			$this->sendEmail($form);
		}

		$this->redirect('this');
	}


	public function sendEmail($form)
	{
		$user = $form->data;
		$absoluteUrls = $this->absoluteUrls;
		$this->absoluteUrls = true;
		$link = $this->link('this', array('key' => $user->user->key));
		$this->absoluteUrls = $absoluteUrls;

		$text = $this->extendedPage->email;
		$text = strtr($text, array(
			'{$email}' => $user->user->email,
			'{$password}' => $form['user']['password']->value,
			'{$link}' => '<a href="' . $link . '">' . $link . '</a>'
		));

		$mail = $this->context->nette->createMail();
		$mail->setFrom("{$this->extendedPage->sender} <{$this->extendedPage->mailFrom}>")
			->addTo($user->user->email)
			->setSubject($this->extendedPage->subject)
			->setHTMLBody($text)
			->send();
	}


	public function renderDefault()
	{
		if ($this->user->isLoggedIn()) {
			$this->flashMessage($this->translator->translate('You are already logged in.'), 'info');
		}

		$this->template->socialLogins = $this->securityManager->getSocialLogins();
	}


	public function renderConfirm()
	{
		if ($this->extendedPage->mode === PageEntity::MODE_MAIL || $this->extendedPage->mode === PageEntity::MODE_MAIL_CHECKUP) {
			$repository = $this->entityManager->getRepository('CmsModule\Pages\Users\UserEntity');
			$user = $repository->findOneByKey($this->key);
			if (!$user) {
				throw new BadRequestException;
			} else {
				$user->enableByKey($this->key);
				$repository->save($user);
				$this->template->user = $user;
			}
		}
	}
}
