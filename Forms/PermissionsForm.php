<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace CmsModule\Forms;

use Venne;
use CmsModule\Administration\AdministrationManager;
use CmsModule\Security\AuthorizatorFactory;
use Venne\Application\PresenterFactory;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class PermissionsForm extends BaseDoctrineForm
{

	/** @var AuthorizatorFactory */
	protected $authorizatorFactory;

	/** @var AdministrationManager */
	protected $administrationManager;

	/** @var PresenterFactory */
	protected $presenterFactory;


	/**
	 * @param AdministrationManager $roleRepository
	 */
	public function setAuthorizatorFactory(AuthorizatorFactory $authorizatorFactory)
	{
		$this->authorizatorFactory = $authorizatorFactory;
	}


	/**
	 * @param AdministrationManager $administrationManager
	 */
	public function setAdministrationManager(AdministrationManager $administrationManager)
	{
		$this->administrationManager = $administrationManager;
	}


	/**
	 * @param PresenterFactory $presenterFactory
	 */
	public function setPresenterFactory(PresenterFactory $presenterFactory)
	{
		$this->presenterFactory = $presenterFactory;
	}


	/**
	 * @param \Nette\ComponentModel\Container $obj
	 */
	protected function attached($obj)
	{
		$presenters = $this->administrationManager->getAdministrationPages();
		/** @var $permissions \Nette\Security\Permission */
		$permissions = $this->authorizatorFactory->getPermissionsByRoles(array($this->entity->name));

		foreach ($this->authorizatorFactory->scanResources() as $class => $page) {
			$presenter = $this->presenterFactory->unformatPresenterClass($class);

			$container = $this->addContainer($this->formatName($class));
			$container->setCurrentGroup($this->addGroup(isset($presenters[$presenter . ':']) ? $presenters[$presenter . ':']['name'] : $class));
			$container->addCheckbox('run', 'Run')->setDefaultValue($permissions->isAllowed($this->entity->name, $class));
		}


		parent::attached($obj);
	}


	public function handleSuccess()
	{
		$values = $this->getValues();
		/** @var $entity \CmsModule\Security\Entities\RoleEntity */
		$entity = $this->entity;

		$entity->getPermissions()->clear();

		$this->addPermissions($values);
	}


	protected function addPermissions($values)
	{
		/** @var $entity \CmsModule\Security\Entities\RoleEntity */
		$entity = $this->entity;

		foreach ($values as $class => $items) {
			if ($items['run']) {
				$entity->permissions[] = new \CmsModule\Security\Entities\PermissionEntity($entity, $this->unformatName($class));
			}

			if ($class !== 'run' && is_array($items)) {
				$this->addPermissions($items);
			}
		}
	}


	protected function formatName($class)
	{
		return str_replace('\\', '_', $class);
	}


	protected function unformatName($name)
	{
		return str_replace('_', '\\', $name);
	}
}
