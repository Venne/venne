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
use CmsModule\Security\Repositories\RoleRepository;
use Nette\Callback;
use DoctrineModule\Repositories\BaseRepository;
use CmsModule\Components\Table\Form;
use CmsModule\Forms\RoleFormFactory;
use CmsModule\Forms\PermissionsFormFactory;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 *
 * @secured
 */
class RolesPresenter extends BasePresenter
{

	/** @var RoleRepository */
	protected $roleRepository;

	/** @var RoleFormFactory */
	protected $roleForm;

	/** @var Callback */
	protected $permissionsForm;


	/**
	 * @param RoleRepository $roleRepository
	 * @param PermissionsFormFactory $permissionsForm
	 */
	function __construct(RoleRepository $roleRepository, PermissionsFormFactory $permissionsForm)
	{
		$this->roleRepository = $roleRepository;
		$this->permissionsForm = $permissionsForm;
	}


	public function injectRoleForm(RoleFormFactory $roleForm)
	{
		$this->roleForm = $roleForm;
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


	public function createComponentTable()
	{
		$admin = new AdminGrid($this->roleRepository);

		// columns
		$table = $admin->getTable();
		$table->setTranslator($this->context->translator->translator);
		$table->addColumn('name', 'Name')
			->setSortable()
			->getCellPrototype()->width = '40%';
		$table->getColumn('name')
			->setFilter()->setSuggestion();

		$table->addColumn('parent', 'Parent')
			->setSortable()
			->getCellPrototype()->width = '60%';
		$table->getColumn('parent')
			->setCustomRender(function (\CmsModule\Security\Entities\RoleEntity $entity) {
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
			$permissionsForm = $admin->createForm($this->permissionsForm, 'Permissions', NULL, \CmsModule\Components\Table\Form::TYPE_LARGE);

			$admin->connectFormWithAction($form, $table->getAction('edit'));
			$admin->connectFormWithAction($permissionsForm, $table->getAction('permissions'));

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
