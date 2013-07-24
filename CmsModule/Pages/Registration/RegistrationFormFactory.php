<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace CmsModule\Pages\Registration;

use CmsModule\Security\SecurityManager;
use DoctrineModule\Forms\FormFactory;
use Venne\Forms\Form;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class RegistrationFormFactory extends FormFactory
{

	/** @var SecurityManager */
	protected $securityManager;


	public function __construct(\DoctrineModule\Forms\Mappers\EntityMapper $mapper, SecurityManager $securityManager)
	{
		parent::__construct($mapper);

		$this->securityManager = $securityManager;
	}


	protected function getControlExtensions()
	{
		return array_merge(parent::getControlExtensions(), array(
			new \FormsModule\ControlExtensions\ControlExtension(),
		));
	}


	/**
	 * @param Form $form
	 */
	public function configure(Form $form)
	{
		$form->addGroup("Settings");

		$form->addSelect('mode', 'Registration mode', PageEntity::getModes())
			->addCondition($form::IS_IN, array(PageEntity::MODE_MAIL, PageEntity::MODE_MAIL_CHECKUP))->toggle('form-group-email');
		$form->addSelect('socialMode', 'Social login mode', PageEntity::getSocialModes());
		$form->addSelect('userType', 'User type', $this->securityManager->getTypes())->setPrompt('------');
		$form->addManyToMany('roles', 'Roles for new user');

		$form->addGroup('E-mail')->setOption('id', 'form-group-email');
		$form->addText('mailFrom', 'From')
			->addConditionOn($form['mode'], $form::IS_IN, array(PageEntity::MODE_MAIL, PageEntity::MODE_MAIL_CHECKUP))
			->addRule($form::EMAIL);
		$form->addText('sender', 'Sender');
		$form->addText('subject', 'Subject');
		$form->addEditor('email', 'E-mail body');

		$form->setCurrentGroup();
		$form->addSaveButton('Save');
	}
}
