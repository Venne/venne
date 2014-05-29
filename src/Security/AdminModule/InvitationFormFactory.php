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
class InvitationFormFactory implements IFormFactory
{

	/** @var IFormFactory */
	private $formFactory;


	public function __construct(IFormFactory $formFactory)
	{
		$this->formFactory = $formFactory;
	}


	public function create()
	{
		$form = $this->formFactory->create();

		$form->addSelect('registration', 'Registration type')
			->setOption(IComponentMapper::ITEMS_TITLE, 'name')
			->addRule($form::FILLED);
		$form->addText('email', 'Email')
			->addRule($form::FILLED)
			->addRule($form::EMAIL);

		$form->addSubmit('_submit', 'Send');

		return $form;
	}

}
