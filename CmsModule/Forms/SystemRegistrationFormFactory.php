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

use CmsModule\Security\Repositories\RoleRepository;
use CmsModule\Security\SecurityManager;
use FormsModule\ControlExtensions\ControlExtension;
use FormsModule\Mappers\ConfigMapper;
use Venne\Forms\Container;
use Venne\Forms\Form;
use Venne\Forms\FormFactory;
use CmsModule\Pages\Registration\PageEntity;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class SystemRegistrationFormFactory extends FormFactory
{

	/** @var ConfigMapper */
	protected $mapper;

	/** @var SecurityManager */
	private $securityManager;

	/** @var RoleRepository */
	private $roleRepository;


	/**
	 * @param ConfigMapper $mapper
	 * @param SecurityManager $securityManager
	 * @param RoleRepository $roleRepository
	 */
	public function __construct(ConfigMapper $mapper, SecurityManager $securityManager, RoleRepository $roleRepository)
	{
		$this->mapper = $mapper;
		$this->securityManager = $securityManager;
		$this->roleRepository = $roleRepository;
	}


	protected function getMapper()
	{
		$mapper = clone $this->mapper;
		$mapper->setRoot('cms.administration');
		return $mapper;
	}


	protected function getControlExtensions()
	{
		return array_merge(parent::getControlExtensions(), array(
			new ControlExtension()
		));
	}


	/**
	 * @param Form $form
	 */
	protected function configure(Form $form)
	{
		$group = $form->addGroup('Registration');

		$userTypes = array();
		foreach ($this->securityManager->getUserTypes() as $name => $val) {
			$userTypes[$name] = $val->getName();
		}

		$registrations = $form->addDynamic('registrations', function (Container $registration) use ($form, $group, $userTypes) {
			$group = $form->addGroup('Registration');

			$registration->setCurrentGroup($group);
			$registration->addCheckbox('enabled', 'Enabled')->addCondition($form::EQUAL, TRUE)->toggle('reg-' . $registration->name);
			$registration->setCurrentGroup($registration->form->addGroup()->setOption('id', 'reg-' . $registration->name));
			$registration->addText('name', 'Name');
			$registration->addSelect('userType', 'Type', $userTypes);
			$registration->addSelect('mode', 'Mode', PageEntity::getModes())
				->addCondition($form::IS_IN, array(PageEntity::MODE_MAIL, PageEntity::MODE_MAIL_CHECKUP))->toggle('email-' . $registration->name);
			$registration->addSelect('loginProviderMode', 'Login provider mode', PageEntity::getSocialModes());
			$registration->addMultiSelect('roles', 'Roles', $this->getRoles());

			$email = $registration->addContainer('email');
			$email->setCurrentGroup($form->addGroup()->setOption('id', 'email-' . $registration->name));
			$email->addText('subject', 'Subject');
			$email->addText('sender', 'Sender');
			$email->addText('from', 'From');
			$email->addTextArea('text', 'Text');

			$registration->addSubmit('_remove', 'Remove')
				->addRemoveOnClick();
		}, 1);

		$registrations->setCurrentGroup($group);
		$registrations->addSubmit('_add', 'Add')
			->addCreateOnClick();

		$form->setCurrentGroup();
		$form->addSaveButton('Save');
	}


	protected function getRoles()
	{
		return $this->roleRepository->fetchPairs('name', 'name');
	}



}
