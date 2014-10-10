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
use Kdyby\Doctrine\Entities\BaseEntity;
use Kdyby\DoctrineForms\EntityFormMapper;
use Nette\Application\UI\Form;
use Nette\Application\UI\Link;
use Nette\Utils\Callback;
use Venne\Forms\FormFactory;
use Venne\Security\SecurityManager;
use Venne\Security\User;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class ResetFormService extends \Nette\Object
{

	const SUBMIT_NAME = '_submit';

	const SUBMIT_CAPTION = 'Reset password';

	/** @var \Venne\Forms\IFormFactory */
	private $formFactory;

	/** @var \Doctrine\ORM\EntityManager */
	private $entityManager;

	/** @var \Kdyby\Doctrine\EntityRepository */
	private $userRepository;

	/** @var \Venne\Security\SecurityManager */
	private $securityManager;

	public function __construct(
		ResetFormFactory $formFactory,
		EntityManager $entityManager,
		SecurityManager $securityManager
	)
	{
		$this->formFactory = $formFactory;
		$this->userRepository = $entityManager->getRepository(User::class);
		$this->securityManager = $securityManager;
	}

	/**
	 * @param callable $resetLinkCallback
	 * @return \Venne\Forms\FormFactory
	 */
	public function getFormFactory($resetLinkCallback)
	{
		return new FormFactory(function () use ($resetLinkCallback) {
			$form = $this->formFactory->create();
			$form->setCurrentGroup();
			$form->addSubmit(static::SUBMIT_NAME, static::SUBMIT_CAPTION);

			$form->onSuccess[] = function (Form $form) use ($resetLinkCallback) {
				if ($form->isSubmitted() === $form[self::SUBMIT_NAME]) {
					$this->save($form, $resetLinkCallback);
				}
			};

			return $form;
		});
	}

	/**
	 * @param \Nette\Application\UI\Form $form
	 * @param callable $resetLinkCallback
	 */
	protected function save(Form $form, $resetLinkCallback)
	{
		/** @var \Venne\Security\User $user */
		$user = $this->userRepository->findOneBy(array('email' => $form['email']->value));

		if (!$user) {
			$form->addError($form->getTranslator()->translate('User with email %email% does not exist.', null, array(
				'email' => $form['email']->value,
			)));

			return;
		}

		$key = $user->resetPassword();
		$url = Callback::invoke($resetLinkCallback, $key);
		$this->securityManager->sendRecoveryUrl($user, $url);
		$this->entityManager->flush($user);
	}

}
