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

use Venne\Forms\IFormFactory;
use Venne\Security\SecurityManager;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class LoginFormFactory implements \Venne\Forms\IFormFactory
{

	/** @var \Venne\Security\SecurityManager */
	private $securityManager;

	/** @var \Venne\Forms\IFormFactory */
	private $formFactory;

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
		$form = $this->formFactory->create();

		$form->addText('username', 'E-mail')->setRequired('Please provide a e-mail.');
		$form->addPassword('password', 'Password')->setRequired('Please provide a password.');
		$form->addSubmit('_submit', 'Sign in')->getControlPrototype()->class[] = 'btn-primary';

		$socialButtons = $form->addContainer('socialButtons');
		foreach ($this->securityManager->getLoginProviders() as $loginProvider) {
			$socialButtons->addSubmit(str_replace(' ', '_', $loginProvider), $loginProvider)
				->setValidationScope(false);
		}

		return $form;
	}

}
