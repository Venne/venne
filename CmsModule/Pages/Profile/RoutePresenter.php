<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace CmsModule\Pages\Profile;

use CmsModule\Content\Presenters\PagePresenter;
use CmsModule\Pages\Users\UserEntity;
use CmsModule\Security\SecurityManager;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class RoutePresenter extends PagePresenter
{

	/** @var SecurityManager */
	private $securityManager;


	/**
	 * @param SecurityManager $securityManager
	 */
	public function injectSecurityManager(SecurityManager $securityManager)
	{
		$this->securityManager = $securityManager;
	}


	protected function startup()
	{
		parent::startup();

		if (!$this->user->isLoggedIn()) {
			$this->redirect('Route', array('special' => 'login', 'backlink' => $this->storeRequest()));
		}

		if (!$this->user->identity instanceof UserEntity) {
			$this->flashMessage($this->translator->translate('This page works only for regular account.'), 'warning');
		}
	}


	protected function createComponentForm()
	{
		$form = $this->securityManager
			->getUserTypeByClass($this->user->identity->class)
			->getFrontFormFactory()
			->invoke($this->extendedUser);

		$form->onSuccess[] = $this->formSuccess;

		return $form;
	}


	public function formSuccess()
	{
		$this->flashMessage($this->translator->translate('User\'s account has been updated.'), 'success');
		$this->redirect('this');
	}
}
