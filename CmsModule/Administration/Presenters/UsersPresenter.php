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
use CmsModule\Content\Entities\UserPageEntity;
use CmsModule\Content\Repositories\PageRepository;
use CmsModule\Forms\UserFormFactory;
use CmsModule\Forms\UserSocialFormFactory;
use CmsModule\Pages\Users\UserEntity;
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

	/** @var PageRepository */
	protected $pageRepository;

	/** @var UserFormFactory */
	protected $form;

	/** @var UserSocialFormFactory */
	protected $socialForm;

	/** @var UserPageEntity */
	protected $extendedPage;


	/**
	 * @param UserRepository $userRepository
	 */
	public function injectUserRepository(UserRepository $userRepository)
	{
		$this->userRepository = $userRepository;
	}


	/**
	 * @param PageRepository $pageRepository
	 */
	public function injectPageRepository(PageRepository $pageRepository)
	{
		$this->pageRepository = $pageRepository;
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


	public function startup()
	{
		parent::startup();

		if (($page = $this->pageRepository->findOneBy(array('special' => 'users'))) === NULL) {
			$this->flashMessage('User page does not exist.', 'warning');
		} else {
			$this->extendedPage = $this->getEntityManager()->getRepository($page->class)->findOneBy(array('page' => $page));
		}
	}


	/**
	 * @secured(privilege="show")
	 */
	public function actionDefault()
	{
		$this->template->extendedPage = $this->extendedPage;
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

			$extendedPage = $this->extendedPage;
			$form = $admin->createForm($this->form, 'User', function () use ($extendedPage) {
				return new UserEntity($extendedPage);
			}, Form::TYPE_LARGE);
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
