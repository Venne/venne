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
use Nette\Application\PresenterFactory;
use Venne\Forms\IFormFactory;
use Venne\Security\AuthorizatorFactory;
use Venne\Security\IControlVerifierReader;
use Venne\System\AdministrationManager;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class PermissionsFormFactory implements IFormFactory
{

	/** @var IFormFactory */
	private $formFactory;

	/** @var AuthorizatorFactory */
	private $authorizatorFactory;

	/** @var AdministrationManager */
	private $administrationManager;

	/** @var PresenterFactory */
	private $presenterFactory;

	/** @var IControlVerifierReader */
	private $reader;

	/** @var EntityDao */
	private $roleDao;


	/**
	 * @param IFormFactory $formFactory
	 * @param EntityDao $roleDao
	 * @param AuthorizatorFactory $authorizatorFactory
	 * @param AdministrationManager $administrationManager
	 * @param PresenterFactory $presenterFactory
	 * @param IControlVerifierReader $reader
	 */
	public function __construct(
		IFormFactory $formFactory,
		EntityDao $roleDao,
		AuthorizatorFactory $authorizatorFactory,
		AdministrationManager $administrationManager,
		PresenterFactory $presenterFactory,
		IControlVerifierReader $reader
	)
	{
		$this->formFactory = $formFactory;
		$this->roleDao = $roleDao;
		$this->authorizatorFactory = $authorizatorFactory;
		$this->administrationManager = $administrationManager;
		$this->presenterFactory = $presenterFactory;
		$this->reader = $reader;
	}


	public function create()
	{
		$form = $this->formFactory->create();

		$presenters = $this->administrationManager->getAdministrationPages();
		/** @var $permissions \Nette\Security\Permission */
		$permissions = $this->authorizatorFactory->getPermissionsByRoles(array($form->data->name));

		foreach ($this->scanResources() as $resource => $privileges) {
			$presenter = $this->presenterFactory->unformatPresenterClass($resource);

			$container = $form->addContainer($this->formatName($resource));
			$container->setCurrentGroup($form->addGroup(isset($presenters[$presenter . ':']) ? $presenters[$presenter . ':']['name'] : $resource));
			$val = $permissions->isAllowed($form->data->name, $resource);
			$container->addCheckbox('all', 'All')->setDefaultValue($val);

			$privilegeContainer = $container->addContainer('privileges');
			foreach ($privileges as $privilege) {
				/** @var $checkbox \Nette\Forms\Controls\Checkbox */
				$checkbox = $privilegeContainer->addCheckbox($privilege, $privilege);
				$checkbox->setDefaultValue($permissions->isAllowed($form->data->name, $resource, $privilege));
				if ($val) {
					$checkbox->disabled = TRUE;
				}
			}
		}

		$form->setCurrentGroup();
		$form->addSubmit('_submit', 'Save');

		return $form;
	}


	public function handleSave($form)
	{
		$values = $form->getValues();
		$entity = $form->data;

		$entity->getPermissions()->clear();

		foreach ($values as $resource => $items) {
			foreach ($items['privileges'] as $privilege => $item) {
				if ($item) {
					$entity->permissions[] = new \Venne\Security\Entities\PermissionEntity($entity, $this->unformatName($resource), $privilege);
				}
			}
		}

		$this->addPermissions($entity, $values);

		$this->roleDao->save($form->data);
	}


	protected function addPermissions($entity, $values)
	{
		foreach ($values as $class => $items) {
			if ($items['all']) {
				$entity->permissions[] = new \Venne\Security\Entities\PermissionEntity($entity, $this->unformatName($class));
			}

			if ($class !== 'all' && is_array($items)) {
				$this->addPermissions($entity, $items);
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


	/**
	 * Array of all resources.
	 *
	 * @return array
	 */
	protected function scanResources()
	{
		$ret = array();

		foreach ($this->presenterFactory->getPresenters() as $class => $name) {
			$schema = $this->reader->getSchema($class);

			foreach ($schema as $item) {
				if (!array_key_exists($item['resource'], $ret)) {
					$ret[$item['resource']] = array();
				}

				$ret[$item['resource']] = array_unique(array_merge($ret[$item['resource']], $item['privilege'] ? (array)$item['privilege'] : array()));
			}
		}

		return $ret;
	}
}
