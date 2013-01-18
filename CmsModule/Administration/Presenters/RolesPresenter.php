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

use Venne;
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

	/** @var BaseRepository */
	protected $roleRepository;

	/** @var RoleFormFactory */
	protected $roleForm;

	/** @var Callback */
	protected $permissionsForm;


	/**
	 * @param BaseRepository $roleRepository
	 * @param PermissionsFormFactory $permissionsForm
	 */
	function __construct(BaseRepository $roleRepository, PermissionsFormFactory $permissionsForm)
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


	public function createComponentTable()
	{
		$table = new \CmsModule\Components\Table\TableControl;
		$table->setTemplateConfigurator($this->templateConfigurator);
		$table->setRepository($this->roleRepository);

		// forms
		$form = $table->addForm($this->roleForm, 'Role');
		$permissionsForm = $table->addForm($this->permissionsForm, 'Permissions', NULL, Form::TYPE_FULL);

		// navbar
		$table->addButtonCreate('create', 'Create new', $form, 'file');

		// columns
		$table->addColumn('name', 'Name')
			->setWidth('40%');
		$table->addColumn('parent', 'Parents')
			->setWidth('60%')
			->setCallback(function (\CmsModule\Security\Entities\RoleEntity $entity) {
				$entities = array();
				$en = $entity;
				while (($en = $en->getParent())) {
					$entities[] = $en->getName();
				}

				return implode(', ', $entities);
			});

		// actions
		$table->addActionEdit('permissions', 'Permissions', $permissionsForm);
		$table->addActionEdit('edit', 'Edit', $form);
		$table->addActionDelete('delete', 'Delete');

		// global actions
		$table->setGlobalAction($table['delete']);

		return $table;
	}
}
