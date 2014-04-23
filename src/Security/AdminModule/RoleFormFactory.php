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

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class RoleFormFactory implements IFormFactory
{

	/** @var IFormFactory */
	private $formFactory;


	/**
	 * @param IFormFactory $formFactory
	 */
	public function __construct(IFormFactory $formFactory)
	{
		$this->formFactory = $formFactory;
	}


	/**
	 * @return \Nette\Forms\Form
	 */
	public function create()
	{
		$form = $this->formFactory->create();

		$form->addText('name', 'Name');
		$form->addSelect('parent', 'Parent')
			->setOption(IComponentMapper::ITEMS_TITLE, 'name')
			->setPrompt('root');

		$form->addSubmit('_submit', 'Save');

		return $form;
	}

}
