<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace CmsModule\Pages\Login;

use CmsModule\Content\Presenters\PagePresenter;
use CmsModule\Security\SecurityManager;
use Nette\Forms\Form;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class RoutePresenter extends PagePresenter
{

	/** @var \CmsModule\Forms\LoginFormFactory */
	protected $loginFormFactory;

	/** @var SecurityManager */
	protected $securityManager;


	/**
	 * @param \CmsModule\Forms\LoginFormFactory $loginFormFactory
	 */
	public function injectLoginFormFactory(\CmsModule\Forms\LoginFormFactory $loginFormFactory)
	{
		$this->loginFormFactory = $loginFormFactory;
	}


	/**
	 * @param SecurityManager $securityManager
	 */
	public function injectSecurityManager(SecurityManager $securityManager)
	{
		$this->securityManager = $securityManager;
	}


	protected function createComponentForm()
	{
		$this->loginFormFactory->setRedirect(NULL);

		$form = $this->loginFormFactory->invoke();
		$form->onSuccess[] = $this->formSuccess;

		return $form;
	}


	public function formSuccess(Form $form)
	{
		if ($this->extendedPage->page) {
			$this->redirect('this', array('route' => $this->extendedPage->page->mainRoute));
		}
		$this->redirect('this');
	}


	public function renderDefault()
	{
		if ($this->user->isLoggedIn()) {
			$this->flashMessage('You are already logged in.', 'info');
		}

		$this->template->socialLogins = $this->securityManager->getSocialLogins();
	}
}
