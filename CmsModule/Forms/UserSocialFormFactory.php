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
use DoctrineModule\Forms\FormFactory;
use Venne\Forms\Form;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class UserSocialFormFactory extends FormFactory
{

	/** @var SecurityManager */
	protected $securityManager;


	/**
	 * @param SecurityManager $securityManager
	 */
	public function injectSecurityManager(SecurityManager $securityManager)
	{
		$this->securityManager = $securityManager;
	}


	/**
	 * @param Form $form
	 */
	protected function configure(Form $form)
	{
		$user = $form->addOne('user');
		$logins = $user->addMany('socialLogins', function (\Venne\Forms\Container $container) use ($form) {
			$container->setCurrentGroup($form->addGroup($container->data->type));
			$container->addSelect('type', 'Type')->setItems($this->securityManager->getSocialLogins(), false);
			$container->addText('uniqueKey', 'Key');

			$container->addSubmit('remove', 'Remove')->addRemoveOnClick();
		});

		$logins->addSubmit('add', 'Add')->addCreateOnClick();

		$form->addSaveButton('Save');
	}


	public function handleSuccess(Form $form)
	{
		$form->getPresenter()->flashMessage($form->presenter->translator->translate('User has been saved'), 'success');
	}
}
