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
use Venne\Forms\FormFactory;
use Venne\Security\SecurityManager;
use Venne\Security\User;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class ConfirmFormService extends \Nette\Object
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
		ConfirmFormFactory $formFactory,
		EntityManager $entityManager,
		SecurityManager $securityManager
	) {
		$this->formFactory = $formFactory;
		$this->entityManager = $entityManager;
		$this->userRepository = $entityManager->getRepository(User::class);
		$this->securityManager = $securityManager;
	}

	/**
	 * @param string $resetKey
	 * @return \Venne\Forms\FormFactory
	 */
	public function getFormFactory($resetKey)
	{
		return new FormFactory(function () use ($resetKey) {
			$form = $this->formFactory->create();
			$form->setCurrentGroup();
			$form->addSubmit(static::SUBMIT_NAME, static::SUBMIT_CAPTION);

			$form->onSuccess[] = function (Form $form) use ($resetKey) {
				if ($form->isSubmitted() === $form[self::SUBMIT_NAME]) {
					$this->save($form, $resetKey);
				}
			};

			return $form;
		});
	}

	/**
	 * @param \Nette\Application\UI\Form $form
	 * @param string $resetKey
	 */
	protected function save(Form $form, $resetKey)
	{
		$user = $this->userRepository->findOneBy(array('resetKey' => $resetKey));
		$user->removeResetKey($resetKey);
		$user->setPassword($form['password']->getValue());

		$this->entityManager->flush();
		$this->securityManager->sendNewPassword($user);
	}

}
