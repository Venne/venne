<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Security\DefaultType;

use Kdyby\DoctrineForms\IComponentMapper;
use Nette\Forms\Form;
use Venne\Forms\IFormFactory;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class AdminFormFactory extends \Nette\Object implements \Venne\Forms\IFormFactory
{

	/** @var \Venne\Forms\IFormFactory */
	private $formFactory;

	public function __construct(IFormFactory $formFactory)
	{
		$this->formFactory = $formFactory;
	}

	/**
	 * @return \Nette\Application\UI\Form
	 */
	public function create()
	{
		$form = $this->formFactory->create();

		$user = $form->addContainer('user');
		$group = $form->addGroup();
		$user->setCurrentGroup($group);
		$user->addCheckbox('published', 'Enable');
		$user->addText('email', 'E-mail')
			->addRule(Form::EMAIL, 'Enter email');
		$user->addText('name', 'Name');
		$user->addTextArea('notation', 'Notation', 40, 4)
			->getControlPrototype()->attrs['class'] = 'input-block-level';
		$user->addMultiSelect('entityRoles', 'Roles')
			->setOption(IComponentMapper::ITEMS_TITLE, 'name');
		$user->addText('key', 'Lock key')
			->setOption('description', 'If is set user cannot log in.');

		$user['password'] = new PasswordContainer();

		return $form;
	}

}
