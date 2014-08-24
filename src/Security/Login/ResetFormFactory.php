<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Security\Login;

use Venne\Forms\IFormFactory;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class ResetFormFactory implements \Venne\Forms\IFormFactory
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

		$form->addText('email', 'Email')
			->addRule($form::FILLED)
			->addRule($form::EMAIL);

		$form->addSubmit('_submit', 'Reset password')
			->getControlPrototype()->class[] = 'btn-primary';
		$form->addSubmit('cancel', 'Cancel')
			->setValidationScope(false);

		return $form;
	}

}
