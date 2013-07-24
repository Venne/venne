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

use CmsModule\Content\Presenters\PagePresenter;
use CmsModule\Security\SecurityManager;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class RoutePresenter extends PagePresenter
{

	/** @persistent */
	public $key;

	/** @var SecurityManager */
	protected $securityManager;


	public function startup()
	{
		parent::startup();

		if (!$this->extendedPage->userType) {
			$this->template->hideForm = true;
			$this->flashMessage('Userform has not been set', 'warning', false);
		}

		if ($this->key) {
			$this->setView('confirm');
		}
	}


	/**
	 * @param SecurityManager $securityManager
	 */
	public function injectSecurityManager(SecurityManager $securityManager)
	{
		$this->securityManager = $securityManager;
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

		$identity = $socialLogin->authenticate(array());
		if ($identity) {
			$this->user->login($identity);
			$this->flashMessage('User is already registered');
			$this->redirect('this');
		}

		$formFactory = $this->securityManager->getFormFactoryByEntity($this->extendedPage->userType);

		if (!$formFactory instanceof \CmsModule\Content\IRegistrationFormFactory) {
			throw new \Nette\InvalidArgumentException("Form factory '" . get_class($formFactory) . "' is not istance of \CmsModule\Content\IRegistrationFormFactory");
		}

		$formFactory->setSocialData($this['form'], $socialLogin);

		if (!$socialLogin->getData()) {
			$this->redirectUrl($socialLogin->getLoginUrl());
		}

		if ($this->extendedPage->getSocialMode() === PageEntity::SOCIAL_MODE_LOAD_AND_SAVE) {
			/** @var $form \Venne\Forms\Form */
			$form = $this['form'];
			$form->onSuccess = NULL;
			$form->setSubmittedBy($form->getSaveButton());
			$form->fireEvents();

			if ($form->isValid()) {
				$socialLogin->connectWithUser($form->getData());

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
		$repository = $this->entityManager->getRepository($this->extendedPage->userType);
		$formFactory = $this->securityManager->getFormFactoryByEntity($this->extendedPage->userType);

		/** @var $entity \CmsModule\Pages\Users\UserEntity */
		$entity = $repository->createNew(array($this->getEntityManager()->getRepository('CmsModule\Pages\Users\PageEntity')->findOneBy(array())));
		if ($this->extendedPage->mode === PageEntity::MODE_BASIC) {
			$entity->setPublished(true);
		} elseif ($this->extendedPage->mode === PageEntity::MODE_MAIL) {
			$entity->setPublished(true);
			$entity->disableByKey();
		} elseif ($this->extendedPage->mode === PageEntity::MODE_MAIL_CHECKUP) {
			$entity->disableByKey();
		}
		foreach ($this->extendedPage->roles as $role) {
			$entity->roleEntities[] = $role;
		}

		$_this = $this;
		$form = $formFactory->invoke($entity);
		$form->onSuccess[] = $this->processSuccess;

		foreach ($this->securityManager->getSocialLogins() as $socialLogin) {
			$form->addSubmit('_submit_' . $socialLogin, $socialLogin)->onClick[] = function () use ($_this, $socialLogin) {
				$_this->handleLogin($socialLogin);
			};
		}

		return $form;
	}


	public function processSuccess($form)
	{
		$this->flashMessage('Your registration is complete', 'success');

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
		$link = $this->link("this", array("key" => $user->key));
		$this->absoluteUrls = $absoluteUrls;

		$text = $this->extendedPage->email;
		$text = strtr($text, array(
			'{$email}' => $user->email,
			'{$password}' => $form["password"]->value,
			'{$link}' => '<a href="' . $link . '">' . $link . '</a>'
		));

		$mail = $this->context->nette->createMail();
		$mail->setFrom("{$this->extendedPage->sender} <{$this->extendedPage->mailFrom}>")
			->addTo($user->email)
			->setSubject($this->extendedPage->subject)
			->setHTMLBody($text)
			->send();
	}


	public function renderDefault()
	{
		if ($this->user->isLoggedIn()){
			$this->flashMessage('You are already logged in.', 'info');
		}

		$this->template->socialLogins = $this->securityManager->getSocialLogins();
	}


	public function renderConfirm()
	{
		if ($this->extendedPage->mode === PageEntity::MODE_MAIL || $this->extendedPage->mode === PageEntity::MODE_MAIL_CHECKUP) {
			$repository = $this->entityManager->getRepository($this->extendedPage->userType);
			$user = $repository->findOneByKey($this->key);
			if (!$user) {
				throw new \Nette\Application\BadRequestException;
			} else {
				$user->enableByKey($this->key);
				$repository->save($user);
				$this->template->user = $user;
			}
		}
	}
}
