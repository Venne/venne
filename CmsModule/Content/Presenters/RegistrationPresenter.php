<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace CmsModule\Content\Presenters;

use Venne;
use CmsModule\Security\SecurityManager;
use Doctrine\ORM\EntityManager;
use CmsModule\Content\Entities\RegistrationPageEntity;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class RegistrationPresenter extends PagePresenter
{

	/** @persistent */
	public $key;

	/** @var SecurityManager */
	protected $securityManager;

	/** @var EntityManager */
	protected $entityManager;


	public function startup()
	{
		parent::startup();

		if (!$this->page->userType) {
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
	 * @param EntityManager $entityManager
	 */
	public function injectEntityManager(EntityManager $entityManager)
	{
		$this->entityManager = $entityManager;
	}


	/**
	 * @return \Doctrine\ORM\EntityRepository
	 */
	protected function getUserRepository()
	{
		return $this->entityManager->getRepository('\CmsModule\Security\Entities\UserEntity');
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

		$formFactory = $this->securityManager->getFormFactoryByEntity($this->page->userType);

		if (!$formFactory instanceof \CmsModule\Content\IRegistrationFormFactory) {
			throw new \Nette\InvalidArgumentException("Form factory '" . get_class($formFactory) . "' is not istance of \CmsModule\Content\IRegistrationFormFactory");
		}

		$formFactory->setSocialData($this['form'], $socialLogin);

		if ($this->page->getSocialMode() === RegistrationPageEntity::SOCIAL_MODE_LOAD_AND_SAVE) {
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
		$repository = $this->entityManager->getRepository($this->page->userType);
		$formFactory = $this->securityManager->getFormFactoryByEntity($this->page->userType);

		/** @var $entity \CmsModule\Security\Entities\UserEntity */
		$entity = $repository->createNew();
		if ($this->page->mode === RegistrationPageEntity::MODE_BASIC) {
			$entity->setEnable(true);
		} elseif ($this->page->mode === RegistrationPageEntity::MODE_MAIL) {
			$entity->setEnable(true);
			$entity->disableByKey();
		} elseif ($this->page->mode === RegistrationPageEntity::MODE_MAIL_CHECKUP) {
			$entity->disableByKey();
		}
		foreach ($this->page->roles as $role) {
			$entity->roleEntities[] = $role;
		}

		$form = $formFactory->invoke($entity);
		$form->onSuccess[] = $this->processSuccess;

		return $form;
	}


	public function processSuccess($form)
	{
		$this->flashMessage('Registration is complete', 'success');

		// email
		if ($this->page->mode === RegistrationPageEntity::MODE_MAIL || $this->page->mode === RegistrationPageEntity::MODE_MAIL_CHECKUP) {
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

		$text = $this->page->email;
		$text = strtr($text, array(
			'{$email}' => $user->email,
			'{$password}' => $form["password"]->value,
			'{$link}' => '<a href="' . $link . '">' . $link . '</a>'
		));

		$mail = $this->context->nette->createMail();
		$mail->setFrom("{$this->page->sender} <{$this->page->mailFrom}>")
			->addTo($user->email)
			->setSubject($this->page->subject)
			->setHTMLBody($text)
			->send();
	}


	public function renderDefault()
	{
		$this->template->socialLogins = $this->securityManager->getSocialLogins();
	}


	public function renderConfirm()
	{
		if ($this->page->mode === RegistrationPageEntity::MODE_MAIL || $this->page->mode === RegistrationPageEntity::MODE_MAIL_CHECKUP) {
			$repository = $this->entityManager->getRepository($this->page->userType);
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
