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

use Kdyby\DoctrineForms\IComponentMapper;
use Venne\Forms\IFormFactory;
use Venne\Security\Role\Role;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class RoleFormFactory extends \Nette\Object implements \Venne\Forms\IFormFactory
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

		$form->addText('name', 'Name');
		$form->addSelect('parent', 'Parent')
			->setOption(IComponentMapper::ITEMS_TITLE, function (Role $role) {
				return $role->getName();
			})
			->setPrompt('root');

		return $form;
	}

}
