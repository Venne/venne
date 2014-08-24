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

use Kdyby\DoctrineForms\IComponentMapper;
use Venne\Forms\IFormFactory;
use Venne\Security\SecurityManager;
use Venne\System\RegistrationEntity;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class RegistrationFormFactory implements \Venne\Forms\IFormFactory
{

	/** @var \Venne\Forms\IFormFactory */
	private $formFactory;

	/** @var \Venne\Security\SecurityManager */
	private $securityManager;

	public function __construct(IFormFactory $formFactory, SecurityManager $securityManager)
	{
		$this->formFactory = $formFactory;
		$this->securityManager = $securityManager;
	}

	/**
	 * @return \Nette\Application\UI\Form
	 */
	public function create()
	{
		\Kdyby\Replicator\Container::register();
		$form = $this->formFactory->create();

		$userTypes = array();
		foreach ($this->securityManager->getUserTypes() as $name => $val) {
			$userTypes[$name] = $val->getName();
		}

		$form->addCheckbox('enabled', 'Enabled');
		$form->addCheckbox('invitation', 'Only as invitation');
		$form->addText('name', 'Name');
		$form->addHidden('key');
		$form->addSelect('userType', 'Type', $userTypes);
		$form->addSelect('mode', 'Mode', RegistrationEntity::getModes());
		$form->addSelect('loginProviderMode', 'Login provider mode', RegistrationEntity::getLoginProviderModes());
		$form->addMultiSelect('roles', 'Roles')
			->setOption(IComponentMapper::ITEMS_TITLE, 'name');

		$form->addSubmit('_submit', 'Save');

		return $form;
	}

}
