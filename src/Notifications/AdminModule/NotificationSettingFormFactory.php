<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Notifications\AdminModule;

use Kdyby\DoctrineForms\IComponentMapper;
use Venne\Forms\IFormFactory;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class NotificationSettingFormFactory implements \Venne\Forms\IFormFactory
{

	/** @var \Venne\Forms\IFormFactory */
	private $formFactory;

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

		$form->addGroup();
		$form->addSelect('user', 'Send notification to')
			->setTranslator()
			->setOption(IComponentMapper::ITEMS_TITLE, 'email');

		$form->addGroup('Criteria');

		$form->addSelect('type', 'Type')
			->setTranslator()
			->setOption(IComponentMapper::ITEMS_TITLE, 'message')
			->setPrompt('all');

		$form->addSelect('targetUser', 'Target user')
			->setTranslator()
			->setOption(IComponentMapper::ITEMS_TITLE, 'email')
			->setPrompt('all');

		return $form;
	}

}
