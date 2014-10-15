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

use Doctrine\ORM\EntityManager;
use Nette\Localization\ITranslator;
use Venne\Security\Role;
use Venne\System\Components\AdminGrid\IAdminGridFactory;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class RolesTableFactory
{

	/** @var \Kdyby\Doctrine\EntityRepository */
	private $roleRepository;

	/** @var \Venne\Security\AdminModule\RoleFormService */
	private $roleFormService;

	/** @var \Venne\System\Components\AdminGrid\IAdminGridFactory */
	private $adminGridFactory;

	/** @var \Nette\Localization\ITranslator */
	private $translator;

	public function __construct(
		EntityManager $entityManager,
		RoleFormService $roleFormService,
		IAdminGridFactory $adminGridFactory,
		ITranslator $translator
	) {
		$this->roleRepository = $entityManager->getRepository(Role::class);
		$this->roleFormService = $roleFormService;
		$this->adminGridFactory = $adminGridFactory;
		$this->translator = $translator;
	}

	/**
	 * @return \Venne\System\Components\AdminGrid\AdminGrid
	 */
	public function create()
	{
		$admin = $this->adminGridFactory->create($this->roleRepository);

		$table = $admin->getTable();
		$table->setTranslator($this->translator);
		$name = $table->addColumnText('name', 'Name');
		$name
			->setSortable()
			->getCellPrototype()->width = '40%';
		$name
			->setFilterText()->setSuggestion();

		$table->addColumnText('parent', 'Parent')
			->setSortable()
			->setCustomRender(function (Role $entity) {
				$entities = array();
				$en = $entity;
				while (($en = $en->getParent())) {
					$entities[] = $en->getName();
				}

				return implode(', ', $entities);
			})
			->getCellPrototype()->width = '60%';

		$form = $admin->addForm('role', 'Role', function (Role $role = null) {
			return $this->roleFormService->getFormFactory($role ? $role->getId() : null);
		});

		$toolbar = $admin->getNavbar();
		$newSection = $toolbar->addSection('new', 'Create', 'file');

		$editAction = $table->addActionEvent('edit', 'Edit');
		$editAction->getElementPrototype()->class[] = 'ajax';

		$permissionAction = $table->addActionEvent('permissions', 'Permissions');
		$permissionAction->getElementPrototype()->class[] = 'ajax';

		$deleteAction = $table->addActionEvent('delete', 'Delete');
		$deleteAction->getElementPrototype()->class[] = 'ajax';

		$admin->connectFormWithNavbar($form, $newSection);
		$admin->connectFormWithAction($form, $editAction);
		$admin->connectActionAsDelete($deleteAction);

		return $admin;
	}

}
