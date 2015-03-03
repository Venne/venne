<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\System\AdminModule;

use Doctrine\ORM\EntityManager;
use Venne\Doctrine\Entities\BaseEntity;
use Kdyby\DoctrineForms\EntityFormMapper;
use Nette\Application\UI\Form;
use Venne\Security\SecurityManager;
use Venne\Security\UserType;
use Venne\System\Registration\LoginProviderMode;
use Venne\System\Registration\Registration;
use Venne\System\Registration\RegistrationMode;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class RegistrationFormService extends \Venne\System\DoctrineFormService
{

	/** @var \Venne\Security\SecurityManager */
	private $securityManager;

	public function __construct(
		RegistrationFormFactory $formFactory,
		EntityManager $entityManager,
		EntityFormMapper $entityFormMapper,
		SecurityManager $securityManager
	) {
		parent::__construct($formFactory, $entityManager, $entityFormMapper);

		$this->securityManager = $securityManager;
	}

	/**
	 * @return string
	 */
	protected function getEntityClassName()
	{
		return Registration::class;
	}

	protected function save(Form $form, $entity)
	{
		/** @var Registration $entity */
		$values = $form->getValues();

		$entity->setName($values->name);
		$entity->setEnabled($values->enabled);
		$entity->setMode(RegistrationMode::get($values->mode));
		$entity->setLoginProviderMode(LoginProviderMode::get($values->loginProviderMode));

		foreach ($entity->getRoles() as $role) {
			$entity->removeRole($role);
		}

		$this->getEntityManager()->persist($entity);
		$this->getEntityManager()->flush($entity);
	}

	protected function load(Form $form, $entity)
	{
		$form->setValues(array(
			'name' => $entity->getName(),
			'enabled' => $entity->isEnabled(),
			'mode' => $entity->getMode()->getValue(),
			'loginProviderMode' => $entity->getLoginProviderMode()->getValue(),
			'roles' => $entity->getRoles(),
		));
	}

	protected function error(Form $form, \Exception $e)
	{
		if ($e instanceof \Kdyby\Doctrine\DuplicateEntryException) {
			$form['name']->addError($form->getTranslator()->translate('Name must be unique.'));

			return;
		}

		parent::error($form, $e);
	}

	/**
	 * @return \Venne\System\Registration\Registration
	 */
	protected function createEntity()
	{
		$userTypes = $this->securityManager->getUserTypes();
		foreach ($userTypes as $type) {
			return new Registration('', $type, RegistrationMode::get(RegistrationMode::BASIC), LoginProviderMode::get(LoginProviderMode::LOAD));
		}
	}

}
