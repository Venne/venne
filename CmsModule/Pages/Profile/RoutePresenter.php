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
use CmsModule\Pages\Users\UserManager;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class RoutePresenter extends PagePresenter
{

	/** @var UserManager */
	private $userManager;


	/**
	 * @param UserManager $userManager
	 */
	public function injectUserManager(UserManager $userManager)
	{
		$this->userManager = $userManager;
	}


	protected function startup()
	{
		parent::startup();

		if (!$this->user->isLoggedIn()) {
			$this->redirect('Route', array('special' => 'login', 'backlink' => $this->storeRequest()));
		}

		if (!$this->user->identity instanceof UserEntity) {
			$this->flashMessage('This page works only for regular account.', 'warning');
		}
	}


	protected function createComponentForm()
	{
		$form = $this->userManager
			->getUserTypeByClass($this->user->identity->class)
			->getFormFactory()
			->invoke($this->extendedUser);

		$form->onSuccess[] = $this->formSuccess;

		return $form;
	}


	public function formSuccess()
	{
		$this->flashMessage('User account has been updated.', 'success');
		$this->redirect('this');
	}
}
