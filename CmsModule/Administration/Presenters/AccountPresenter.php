<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace CmsModule\Administration\Presenters;

use CmsModule\Administration\Components\AdminGrid\AdminGrid;
use CmsModule\Forms\SystemAccountFormFactory;
use CmsModule\Forms\UserFormFactory;
use CmsModule\Security\Entities\LoginEntity;
use CmsModule\Security\Entities\UserEntity;
use CmsModule\Security\Repositories\LoginRepository;
use CmsModule\Security\Repositories\UserRepository;
use Grido\DataSources\Doctrine;
use Nette\Http\Session;
use Nette\Utils\Html;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 *
 * @secured
 */
class AccountPresenter extends BasePresenter
{

	/** @var UserRepository */
	protected $userRepository;

	/** @var LoginRepository */
	protected $loginRepository;

	/** @var SystemAccountFormFactory */
	protected $accountFormFactory;

	/** @var UserFormFactory */
	protected $userFormFactory;

	/** @var Session */
	protected $session;


	/**
	 * @param \CmsModule\Security\Repositories\UserRepository $userRepository
	 */
	public function injectUserRepository(UserRepository $userRepository)
	{
		$this->userRepository = $userRepository;
	}


	/**
	 * @param \CmsModule\Security\Repositories\LoginRepository $loginRepository
	 */
	public function injectLoginRepository(LoginRepository $loginRepository)
	{
		$this->loginRepository = $loginRepository;
	}


	/**
	 * @param \CmsModule\Forms\SystemAccountFormFactory $accountFormFactory
	 */
	public function injectAccountFormFactory(SystemAccountFormFactory $accountFormFactory)
	{
		$this->accountFormFactory = $accountFormFactory;
	}


	/**
	 * @param \CmsModule\Forms\UserFormFactory $userFormFactory
	 */
	public function injectUserFormFactory(UserFormFactory $userFormFactory)
	{
		$this->userFormFactory = $userFormFactory;
	}


	/**
	 * @param Session $session
	 */
	public function injectSession(Session $session)
	{
		$this->session = $session;
	}


	/**
	 * @secured(privilege="show")
	 */
	public function actionDefault()
	{
	}


	/**
	 * @secured
	 */
	public function actionEdit()
	{
	}


	/**
	 * @secured
	 */
	public function actionAdvanced()
	{
	}


	protected function createComponentTable()
	{
		$session = $this->session;
		$admin = new AdminGrid($this->loginRepository);

		// columns
		$table = $admin->getTable();
		if ($this->user->identity instanceof UserEntity) {
			$table->setModel(new Doctrine($this->loginRepository->createQueryBuilder('a')->andWhere('a.user = :user')->setParameter('user', $this->user->identity)));
		} else {
			$table->setModel(new Doctrine($this->loginRepository->createQueryBuilder('a')->andWhere('a.user IS NULL')));
		}
		$table->setTranslator($this->context->translator->translator);
		$table->addColumnDate('current', 'Current')
			->setCustomRender(function (LoginEntity $entity) use ($session) {
				$el = Html::el('span');
				$el->class[] = 'icon ' . ($session->id == $entity->getSessionId() ? 'icon-ok' : 'icon-remove');
				return $el;
			})
			->getCellPrototype()->width = '10%';
		$table->addColumnDate('created', 'Date')
			->setSortable()
			->getCellPrototype()->width = '40%';
		$table->addColumn('sessionId', 'Session ID')
			->getCellPrototype()->width = '50%';

		// actions
		$table->addAction('delete', 'Delete')
			->setConfirm(function ($entity) {
				return "Really delete session '{$entity->sessionId}'?";
			})
			->getElementPrototype()->class[] = 'ajax';
		$admin->connectActionAsDelete($table->getAction('delete'));

		return $admin;
	}


	protected function createComponentAccountForm()
	{
		if ($this->user->identity instanceof UserEntity) {
			$form = $this->userFormFactory->invoke($this->user->identity);
			if (!$this->isAuthorized('advanced')) {
				$form['email']->setDisabled();
				$form['key']->setDisabled();
				$form['enable']->setDisabled();
				$form['roleEntities']->setDisabled();
			}
		} else {
			$form = $this->accountFormFactory->invoke();
		}
		$form->onSuccess[] = function ($form) {
			$form->getPresenter()->flashMessage("Account settings has been updated", "success");
			$form->getPresenter()->redirect("this");
		};
		return $form;
	}
}
