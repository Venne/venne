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

use Kdyby\Doctrine\EntityDao;
use Nette\Forms\Container;
use Venne\Forms\IFormFactory;
use Venne\Security\SecurityManager;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class RegistrationFormFactory implements IFormFactory
{

	const MODE_BASIC = 'basic';

	const MODE_CHECKUP = 'checkup';

	const MODE_MAIL = 'mail';

	const MODE_MAIL_CHECKUP = 'mail&checkup';

	const SOCIAL_MODE_LOAD = 'load';

	const SOCIAL_MODE_LOAD_AND_SAVE = 'load&save';

	private static $modes = array(
		self::MODE_BASIC => 'basic registration',
		self::MODE_CHECKUP => 'registration with admin confirmation',
		self::MODE_MAIL => 'registration with e-mail confirmation',
		self::MODE_MAIL_CHECKUP => 'registration with e-mail and admin confirmation'
	);

	private static $socialModes = array(
		self::SOCIAL_MODE_LOAD => 'only load user data',
		self::SOCIAL_MODE_LOAD_AND_SAVE => 'load user data and save',
	);

	/** @var EntityDao */
	private $roleDao;

	/** @var IFormFactory */
	private $formFactory;

	/** @var SecurityManager */
	private $securityManager;


	/**
	 * @param IFormFactory $formFactory
	 * @param EntityDao $roleDao
	 * @param SecurityManager $securityManager
	 */
	public function __construct(IFormFactory $formFactory, EntityDao $roleDao, SecurityManager $securityManager)
	{
		$this->formFactory = $formFactory;
		$this->roleDao = $roleDao;
		$this->securityManager = $securityManager;
	}


	public function create()
	{
		\Kdyby\Replicator\Container::register();
		$form = $this->formFactory->create();

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
			$registration->addCheckbox('byRequest', 'By request');
			$registration->addText('name', 'Name');
			$registration->addSelect('userType', 'Type', $userTypes);
			$registration->addSelect('mode', 'Mode', self::$modes)
				->addCondition($form::IS_IN, array(self::MODE_MAIL, self::MODE_MAIL_CHECKUP))->toggle('email-' . $registration->name);
			$registration->addSelect('loginProviderMode', 'Login provider mode', self::$socialModes);
			$registration->addMultiSelect('roles', 'Roles', $this->getRoles());

			$email = $registration->addContainer('email');
			$email->setCurrentGroup($group = $form->addGroup()->setOption('container', \Nette\Utils\Html::el('div')->id('email-' . $registration->name)));
			$email->addText('subject', 'Subject');
			$email->addText('sender', 'Sender');
			$email->addText('from', 'From');
			$email->addTextArea('text', 'Text');

			$registration->setCurrentGroup($group);
			$registration->addSubmit('_remove', 'Remove')
				->addRemoveOnClick();
		});

		$registrations->setCurrentGroup($group);
		$registrations->addSubmit('_add', 'Add')
			->addCreateOnClick();

		$form->setCurrentGroup();
		$form->addSubmit('_submit', 'Save');

		return $form;
	}


	protected function getRoles()
	{
		return $this->roleDao->findAssoc(array(), 'name');
	}

}
