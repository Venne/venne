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
use CmsModule\Content\Entities\PermissionEntity;
use CmsModule\Security\Repositories\RoleRepository;
use DoctrineModule\Forms\FormFactory;
use Venne\Forms\Form;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class PermissionsFormFactory extends FormFactory
{

	/** @var array */
	private $_roles;


	/**
	 * @param Form $form
	 */
	public function configure(Form $form)
	{
		$form->addOne('page')->setCurrentGroup($form->addGroup('Global'))->addCheckbox('secured', 'Enable permissions');

		if ($form->data->page->secured) {
			$permissions = $form->addContainer('permissions');
			foreach ($form->data->getPrivileges() as $key => $name) {
				$container = $permissions->addContainer($key);
				$container->setCurrentGroup($form->addGroup($name));
				$container->addMultiSelect('permissions', '', $this->getRoles());
			}
		}

		$form->setCurrentGroup();
		$form->addSaveButton('Save');
	}


	public function handleLoad(Form $form)
	{
		if ($form->data->page->secured) {
			foreach ($form->data->page->permissions as $key => $permission) {
				$items = array();
				foreach ($permission->roles as $role) {
					$items[] = $role->id;
				}
				$form['permissions'][$key]['permissions']->setDefaultValue($items);
			}
		}
	}


	public function handleSave(Form $form)
	{
		if ($form->data->page->secured) {
			/** @var ExtendedPageEntity $entity */
			$entity = $form->data;
			$entity->getPage()->getPermissions()->clear();
			$form->mapper->entityManager->flush($entity);

			foreach ($form->values['permissions'] as $key => $permission) {
				$permissionEntity = new PermissionEntity;
				$permissionEntity->setName($key);
				$permissionEntity->setPage($entity->getPage());

				foreach ($permission['permissions'] as $id) {
					$permissionEntity->roles[$id] = $this->getRoleRepository()->find($id);
				}

				$entity->getPage()->permissions[$key] = $permissionEntity;
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
