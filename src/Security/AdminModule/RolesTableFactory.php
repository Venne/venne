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
use Nette\Localization\ITranslator;
use Venne\Security\RoleEntity;
use Venne\System\Components\AdminGrid\Form;
use Venne\System\Components\AdminGrid\IAdminGridFactory;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class RolesTableFactory
{

	/** @var \Kdyby\Doctrine\EntityDao */
	private $dao;

	/** @var \Venne\Security\AdminModule\RoleFormFactory */
	private $roleForm;

	/** @var \Venne\Security\AdminModule\PermissionsFormFactory */
	private $permissionsForm;

	/** @var \Venne\System\Components\AdminGrid\IAdminGridFactory */
	private $adminGridFactory;

	/** @var \Nette\Localization\ITranslator */
	private $translator;

	public function __construct(
		EntityDao $dao,
		PermissionsFormFactory $permissionsForm,
		RoleFormFactory $roleForm,
		IAdminGridFactory $adminGridFactory,
		ITranslator $translator
	)
	{
		$this->dao = $dao;
		$this->permissionsForm = $permissionsForm;
		$this->roleForm = $roleForm;
		$this->adminGridFactory = $adminGridFactory;
		$this->translator = $translator;
	}

	/**
	 * @return \Venne\System\Components\AdminGrid\AdminGrid
	 */
	public function create()
	{
		$admin = $this->adminGridFactory->create($this->dao);

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
		$table->addActionEvent('edit', 'Edit')
			->getElementPrototype()->class[] = 'ajax';

		$table->addActionEvent('permissions', 'Permissions')
			->getElementPrototype()->class[] = 'ajax';

		$form = $admin->addForm('role', 'Role', $this->roleForm);
		$permissionsForm = $admin->addForm('permissions', 'Permissions', $this->permissionsForm, null, Form::TYPE_LARGE);

		$admin->connectFormWithAction($form, $table->getAction('edit'));
		$admin->connectFormWithAction($permissionsForm, $table->getAction('permissions'), $admin::MODE_PLACE);

		// Toolbar
		$toolbar = $admin->getNavbar();
		$toolbar->addSection('new', 'Create', 'file');
		$admin->connectFormWithNavbar($form, $toolbar->getSection('new'));

		$table->addActionEvent('delete', 'Delete')
			->getElementPrototype()->class[] = 'ajax';
		$admin->connectActionAsDelete($table->getAction('delete'));

		return $admin;
	}

}
