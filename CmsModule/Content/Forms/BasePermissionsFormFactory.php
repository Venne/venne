<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace CmsModule\Content\Forms;

use CmsModule\Content\Entities\ExtendedPageEntity;
use CmsModule\Security\Repositories\RoleRepository;
use DoctrineModule\Forms\FormFactory;
use Venne\Forms\Form;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class BasePermissionsFormFactory extends FormFactory
{

	/** @var array */
	private $_roles;


	/**
	 * @param Form $form
	 */
	public function configure(Form $form)
	{
		$form->addOne('page')
			->setCurrentGroup($form->addGroup('Global'))
			->addCheckbox($this->getSecuredColumnName(), 'Enable permissions');

		if ($form->data->page->{$this->getSecuredColumnName()}) {
			$permissions = $form->addContainer('permissions');
			foreach ($this->getPrivileges($form) as $key => $name) {
				$container = $permissions->addContainer($key);
				$container->setCurrentGroup($form->addGroup(ucfirst($name)));
				$container->addCheckbox('all', 'Allow for all')->addCondition($form::EQUAL, FALSE)->toggle($container->name . $key);

				$group = $form->addGroup()->setOption('id', $container->name . $key);
				$container->setCurrentGroup($group);
				$container->addMultiSelect('permissions', 'Roles', $this->getRoles());
			}
		}

		$form->setCurrentGroup();
		$form->addSaveButton('Save');
	}


	protected function getPrivileges(Form $form)
	{
		return $form->data->getPrivileges();
	}

	protected function getPermissionColumnName()
	{
		return 'permissions';
	}

	protected function getPermissionEntityName()
	{
		return 'CmsModule\Content\Entities\PermissionEntity';
	}

	protected function getSecuredColumnName()
	{
		return 'secured';
	}


	public function handleLoad(Form $form)
	{
		if ($form->data->page->{$this->getSecuredColumnName()}) {
			foreach ($form->data->page->{$this->getPermissionColumnName()} as $key => $permission) {
				$items = array();
				foreach ($permission->roles as $role) {
					$items[] = $role->id;
				}
				$form['permissions'][$key]['all']->setDefaultValue($permission->all);
				$form['permissions'][$key]['permissions']->setDefaultValue($items);
			}
		}
	}


	public function handleSave(Form $form)
	{
		if ($form->data->page->{$this->getSecuredColumnName()}) {
			/** @var ExtendedPageEntity $entity */
			$entity = $form->data;
			$entity->getPage()->getPermissions()->clear();
			$form->mapper->entityManager->flush($entity);

			if (isset($form->values['permissions'])) {
				foreach ($form->values['permissions'] as $key => $permission) {
					$class = $this->getPermissionEntityName();
					$permissionEntity = new $class;
					$permissionEntity->setName($key);
					$permissionEntity->setPage($entity->getPage());
					$permissionEntity->setAll($permission['all']);

					foreach ($permission['permissions'] as $id) {
						$permissionEntity->roles[$id] = $this->getRoleRepository()->find($id);
					}

					$entity->getPage()->{$this->getPermissionColumnName()}[$key] = $permissionEntity;
				}
			}
		}

		parent::handleSave($form);
	}


	/**
	 * @return RoleRepository
	 */
	private function getRoleRepository()
	{
		return $this->mapper->getEntityManager()->getRepository('CmsModule\Security\Entities\RoleEntity');
	}


	/**
	 * @return array
	 */
	private function getRoles()
	{
		if ($this->_roles === NULL) {
			foreach ($this->getRoleRepository()->findAll() as $role) {
				$this->_roles[$role->id] = (string)$role;
			}
		}

		return $this->_roles;
	}
}
