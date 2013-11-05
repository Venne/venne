<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace CmsModule\Forms;

use CmsModule\Security\SecurityManager;
use Venne\Forms\Form;
use Venne\Forms\FormFactory;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class LoginFormFactory extends FormFactory
{

	/** @var SecurityManager */
	private $securityManager;


	/**
	 * @param SecurityManager $securityManager
	 */
	public function __construct(SecurityManager $securityManager)
	{
		$this->securityManager = $securityManager;
	}


	/**
	 * @param Form $form
	 */
	public function configure(Form $form)
	{
		$form->addText('username', 'E-mail')->setRequired('Please provide a e-mail.');
		$form->addPassword('password', 'Password')->setRequired('Please provide a password.');
		$form->addCheckbox('remember', 'Remember me on this computer');
		$form->addSaveButton('Sign in')->getControlPrototype()->class[] = 'btn-primary';

		$socialButtons = $form->addContainer('socialButtons');
		foreach ($this->securityManager->getLoginProviders() as $loginProvider) {
			$socialButtons->addSubmit(str_replace(' ', '_', $loginProvider), $loginProvider)
				->setValidationScope(FALSE);
		}
	}

}
