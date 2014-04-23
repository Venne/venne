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

use Kdyby\Doctrine\EntityDao;
use Nette\Application\UI\Presenter;
use Venne\Bridges\Kdyby\DoctrineForms\FormFactoryFactory;
use Venne\Security\RoleEntity;
use Venne\System\Components\AdminGrid\Form;
use Venne\System\Components\AdminGrid\IAdminGridFactory;
use Venne\System\AdminPresenterTrait;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 *
 * @secured
 */
class RolesPresenter extends Presenter
{

	use AdminPresenterTrait;

	/** @var EntityDao */
	private $roleDao;

	/** @var RoleFormFactory */
	private $roleForm;

	/** @var PermissionsFormFactory */
	private $permissionsForm;

	/** @var IAdminGridFactory */
	private $adminGridFactory;

	/** @var FormFactoryFactory */
	private $formFactoryFactory;


	/**
	 * @param EntityDao $roleDao
	 * @param PermissionsFormFactory $permissionsForm
	 * @param RoleFormFactory $roleForm
	 * @param IAdminGridFactory $adminGridFactory
	 * @param FormFactoryFactory $formFactoryFactory
	 */
	public function __construct(
		EntityDao $roleDao,
		PermissionsFormFactory $permissionsForm,
		RoleFormFactory $roleForm,
		IAdminGridFactory $adminGridFactory,
		FormFactoryFactory $formFactoryFactory
	) {
		$this->roleDao = $roleDao;
		$this->permissionsForm = $permissionsForm;
		$this->roleForm = $roleForm;
		$this->adminGridFactory = $adminGridFactory;
		$this->formFactoryFactory = $formFactoryFactory;
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
		$admin = $this->adminGridFactory->create($this->roleDao);

		// columns
		$table = $admin->getTable();
		$table->setTranslator($this->translator);
		$table->addColumnText('name', 'Name')
			->setSortable()
			->getCellPrototype()->width = '40%';
		$table->getColumn('name')
			->setFilterText()->setSuggestion();

		$table->addColumnText('parent', 'Parent')
			->setSortable()
			->getCellPrototype()->width = '60%';
		$table->getColumn('parent')
			->setCustomRender(function (RoleEntity $entity) {
				$entities = array();
				$en = $entity;
				while (($en = $en->getParent())) {
					$entities[] = $en->getName();
				}

				return implode(', ', $entities);
			});

		// actions
		if ($this->isAuthorized('edit')) {
			$table->addAction('edit', 'Edit')
				->getElementPrototype()->class[] = 'ajax';

			$table->addAction('permissions', 'Permissions')
				->getElementPrototype()->class[] = 'ajax';

			$form = $admin->createForm($this->roleForm, 'Role');
			$permissionsForm = $admin->createForm($this->permissionsForm, 'Permissions', NULL, Form::TYPE_LARGE);

			$admin->connectFormWithAction($form, $table->getAction('edit'));
			$admin->connectFormWithAction($permissionsForm, $table->getAction('permissions'), $admin::MODE_PLACE);

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
