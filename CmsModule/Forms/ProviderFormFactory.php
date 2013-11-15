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

use CmsModule\Pages\Users\UserEntity;
use CmsModule\Security\SecurityManager;
use Venne\Forms\Form;
use Venne\Forms\FormFactory;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class ProviderFormFactory extends FormFactory
{

	/** @var array */
	public $onSave;

	/** @var array */
	public $onSuccess;

	/** @var string */
	private $provider;

	/** @var UserEntity */
	private $user;

	/** @var SecurityManager */
	private $securityManager;


	/**
	 * @param \CmsModule\Security\SecurityManager $securityManager
	 */
	public function injectSecurityManager(SecurityManager $securityManager)
	{
		$this->securityManager = $securityManager;
	}


	/**
	 * @param string $provider
	 */
	public function setProvider($provider)
	{
		$this->provider = $provider;
	}


	/**
	 * @param \CmsModule\Pages\Users\UserEntity $user
	 */
	public function setUser(UserEntity $user)
	{
		$this->user = $user;
	}


	/**
	 * @param Form $form
	 */
	public function configure(Form $form)
	{
		$form->addHidden('provider')->setValue($this->provider);
		$form['parameters'] = $this->securityManager
			->getLoginProviderByName($this->provider)
			->getFormContainer();

		$form->addSaveButton('Sign in')
			->getControlPrototype()->class[] = 'btn-primary';
		$form->addSubmit('cancel', 'Cancel')
			->setValidationScope(FALSE);
	}


	public function handleSave(Form $form)
	{
		$this->onSave($form);
	}


	public function handleSuccess(Form $form)
	{
		$this->onSuccess($form);
	}

}
