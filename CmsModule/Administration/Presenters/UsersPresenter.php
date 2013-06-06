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
use CmsModule\Components\Table\Form;
use CmsModule\Forms\UserFormFactory;
use CmsModule\Forms\UserSocialFormFactory;
use CmsModule\Security\Repositories\UserRepository;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 *
 * @secured
 */
class UsersPresenter extends BasePresenter
{

	/** @persistent */
	public $page;

	/** @var UserRepository */
	protected $userRepository;

	/** @var UserFormFactory */
	protected $form;

	/** @var UserSocialFormFactory */
	protected $socialForm;


	/**
	 * @param \CmsModule\Security\Repositories\UserRepository $userRepository
	 */
	public function injectUserRepository(UserRepository $userRepository)
	{
		$this->userRepository = $userRepository;
	}


	/**
	 * @param UserFormFactory $form
	 */
	public function injectForm(UserFormFactory $form)
	{
		$this->form = $form;
	}


	/**
	 * @param UserSocialFormFactory $socialForm
	 */
	public function injectSocialForm(UserSocialFormFactory $socialForm)
	{
		$this->socialForm = $socialForm;
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
	public function actionCreate()
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
	public function actionRemove()
	{
	}


	protected function createComponentTable()
	{
		$admin = new AdminGrid($this->userRepository);

		// columns
		$table = $admin->getTable();
		$table->setTranslator($this->context->translator->translator);
		$table->addColumn('email', 'E-mail')
			->setSortable()
			->getCellPrototype()->width = '60%';
		$table->getColumn('email')
			->setFilter()->setSuggestion();

		$table->addColumn('roles', 'Roles')
			->setSortable()
			->getCellPrototype()->width = '40%';
		$table->getColumn('roles')
			->setCustomRender(function ($entity) {
				return implode(", ", $entity->roles);
			});

		// actions
		if ($this->isAuthorized('edit')) {
			$table->addAction('edit', 'Edit')
				->getElementPrototype()->class[] = 'ajax';

			$table->addAction('socialLogins', 'Social Logins')
				->getElementPrototype()->class[] = 'ajax';

			$form = $admin->createForm($this->form, 'User', NULL, Form::TYPE_LARGE);
			$socialForm = $admin->createForm($this->socialForm, 'Social Logins', NULL, Form::TYPE_LARGE);

			$admin->connectFormWithAction($form, $table->getAction('edit'));
			$admin->connectFormWithAction($socialForm, $table->getAction('socialLogins'));

			// Toolbar
			$toolbar = $admin->getNavbar();
			$toolbar->addSection('new', 'Create', 'file');
			$admin->connectFormWithNavbar($form, $toolbar->getSection('new'));
		}

		if ($this->isAuthorized('remove')) {
			$table->addAction('delete', 'Delete')
				->getElementPrototype()->class[] = 'ajax';
			$admin->connectActionAsDelete($table->getAction('delete'));
		}

		return $admin;
	}
}
