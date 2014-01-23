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

use CmsModule\Administration\AdministrationManager;
use CmsModule\Security\AuthorizatorFactory;
use CmsModule\Security\Repositories\RoleRepository;
use DoctrineModule\Forms\Mappers\EntityMapper;
use Nette\Application\PresenterFactory;
use Venne\Forms\Form;
use Venne\Forms\FormFactory;
use Venne\Security\IControlVerifierReader;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class PermissionsFormFactory extends FormFactory
{

	/** @var AuthorizatorFactory */
	protected $authorizatorFactory;

	/** @var AdministrationManager */
	protected $administrationManager;

	/** @var PresenterFactory */
	protected $presenterFactory;

	/** @var IControlVerifierReader */
	protected $reader;

	/** @var EntityMapper */
	protected $mapper;

	/** @var RoleRepository */
	protected $repository;


	/**
	 * @param EntityMapper $mapper
	 * @param RoleRepository $repository
	 */
	public function __construct(EntityMapper $mapper, RoleRepository $repository)
	{
		$this->mapper = $mapper;
		$this->repository = $repository;
	}


	protected function getMapper()
	{
		return $this->mapper;
	}


	/**
	 * @param AuthorizatorFactory $authorizatorFactory
	 */
	public function injectAuthorizatorFactory(AuthorizatorFactory $authorizatorFactory)
	{
		$this->authorizatorFactory = $authorizatorFactory;
	}


	/**
	 * @param AdministrationManager $administrationManager
	 */
	public function injectAdministrationManager(AdministrationManager $administrationManager)
	{
		$this->administrationManager = $administrationManager;
	}


	/**
	 * @param PresenterFactory $presenterFactory
	 */
	public function injectPresenterFactory(PresenterFactory $presenterFactory)
	{
		$this->presenterFactory = $presenterFactory;
	}


	/**
	 * @param IControlVerifierReader $reader
	 */
	public function injectControlVerifier(IControlVerifierReader $reader)
	{
		$this->reader = $reader;
	}


	/**
	 * @param Form $form
	 */
	protected function configure(Form $form)
	{
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
		$form->addSaveButton('Save');
	}


	public function handleSave($form)
	{
		$values = $form->getValues();
		$entity = $form->data;

		$entity->getPermissions()->clear();

		foreach ($values as $resource => $items) {
			foreach ($items['privileges'] as $privilege => $item) {
				if ($item) {
					$entity->permissions[] = new \CmsModule\Security\Entities\PermissionEntity($entity, $this->unformatName($resource), $privilege);
				}
			}
		}

		$this->addPermissions($entity, $values);

		$this->repository->save($form->data);
	}


	protected function addPermissions($entity, $values)
	{
		foreach ($values as $class => $items) {
			if ($items['all']) {
				$entity->permissions[] = new \CmsModule\Security\Entities\PermissionEntity($entity, $this->unformatName($class));
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
