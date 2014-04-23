<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Security\AdminModule;

use Grido\DataSources\Doctrine;
use Kdyby\Doctrine\EntityDao;
use Nette\Application\UI\Presenter;
use Venne\Security\DefaultType\AdminFormFactory;
use Venne\Security\SecurityManager;
use Venne\System\AdminPresenterTrait;
use Venne\System\Components\AdminGrid\AdminGrid;
use Venne\System\Components\AdminGrid\Form;
use Venne\System\Components\AdminGrid\IAdminGridFactory;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 *
 * @secured
 */
class UsersPresenter extends Presenter
{

	use AdminPresenterTrait;

	/** @persistent */
	public $page;

	/** @persistent */
	public $type;

	/** @var EntityDao */
	private $userDao;

	/** @var AdminFormFactory */
	private $form;

	/** @var ProviderFormFactory */
	private $providerForm;

	/** @var SecurityManager */
	private $securityManager;

	/** @var IAdminGridFactory */
	private $adminGridFactory;


	/**
	 * @param EntityDao $userDao
	 * @param AdminFormFactory $form
	 * @param ProviderFormFactory $providerForm
	 * @param SecurityManager $securityManager
	 * @param IAdminGridFactory $adminGridFactory
	 */
	public function __construct(
		EntityDao $userDao,
		AdminFormFactory $form,
		ProviderFormFactory $providerForm,
		SecurityManager $securityManager,
		IAdminGridFactory $adminGridFactory
	)
	{
		$this->userDao = $userDao;
		$this->form = $form;
		$this->providerForm = $providerForm;
		$this->securityManager = $securityManager;
		$this->adminGridFactory = $adminGridFactory;
	}


	/**
	 * @return SecurityManager
	 */
	public function getSecurityManager()
	{
		return $this->securityManager;
	}


	protected function startup()
	{
		parent::startup();

		if (!$this->type) {
			$this->type = key($this->securityManager->getUserTypes());
		}
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
		$dao = $this->entityManager->getDao($this->type);
		$admin = $this->adminGridFactory->create($dao);
		$table = $admin->getTable();
		$table->setTranslator($this->translator);
		$table->setModel(new Doctrine($dao->createQueryBuilder('a')
				->addSelect('u')
				->innerJoin('a.user', 'u'),
			array('email' => 'u.email')
		));

		// columns
		$table->addColumnText('email', 'E-mail')
			->setCustomRender(function ($entity) {
				return $entity->user->email;
			})
			->setSortable()
			->getCellPrototype()->width = '60%';
		$table->getColumn('email')
			->setFilterText()->setSuggestion();

		$table->addColumnText('roles', 'Roles')
			->getCellPrototype()->width = '40%';
		$table->getColumn('roles')
			->setCustomRender(function ($entity) {
				return implode(", ", $entity->user->roles);
			});

		// actions
		if ($this->isAuthorized('edit')) {
			$table->addAction('edit', 'Edit')
				->getElementPrototype()->class[] = 'ajax';

			$table->addAction('loginProviders', 'Login providers')
				->getElementPrototype()->class[] = 'ajax';

			$type = $this->type;
			$form = $admin->createForm($this->getUserType()->getFormFactory(), 'User', function () use ($type) {
				return new $type;
			}, Form::TYPE_LARGE);
			$providerForm = $admin->createForm($this->providerForm, 'Login providers', NULL, Form::TYPE_LARGE);

			$admin->connectFormWithAction($form, $table->getAction('edit'), $admin::MODE_PLACE);
			$admin->connectFormWithAction($providerForm, $table->getAction('loginProviders'));

			// Toolbar
			$toolbar = $admin->getNavbar();
			$toolbar->addSection('new', 'Create', 'file');
			$admin->connectFormWithNavbar($form, $toolbar->getSection('new'), $admin::MODE_PLACE);
		}

		if ($this->isAuthorized('remove')) {
			$table->addAction('delete', 'Delete')
				->getElementPrototype()->class[] = 'ajax';
			$admin->connectActionAsDelete($table->getAction('delete'));
		}

		return $admin;
	}


	/**
	 * @return \Venne\System\Pages\Users\UserType
	 */
	private function getUserType()
	{
		return $this->securityManager->getUserTypeByClass($this->type);
	}
}
