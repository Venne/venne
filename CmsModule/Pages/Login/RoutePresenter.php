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

use CmsModule\Components\LoginControl;
use CmsModule\Components\LoginControlFactory;
use CmsModule\Content\Presenters\PagePresenter;
use CmsModule\Security\SecurityManager;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class RoutePresenter extends PagePresenter
{

	/** @persistent */
	public $backlink;

	/** @var LoginControlFactory */
	protected $loginControlFactoy;

	/** @var SecurityManager */
	protected $securityManager;


	/**
	 * @param LoginControl $loginControlFactoy
	 */
	public function injectLoginControlFactoy(LoginControlFactory $loginControlFactoy)
	{
		$this->loginControlFactoy = $loginControlFactoy;
	}


	/**
	 * @param SecurityManager $securityManager
	 */
	public function injectSecurityManager(SecurityManager $securityManager)
	{
		$this->securityManager = $securityManager;
	}


	protected function createComponentLoginForm()
	{
		$form = $this->loginControlFactoy->invoke();
		$form->onSuccess[] = $this->formSuccess;
		$form->onError[] = $this->formError;

		if ($this->extendedPage->resetEnabled) {
			$form->setResetEmail(
				$this->extendedPage->resetSubject,
				$this->extendedPage->resetText,
				$this->extendedPage->resetSender,
				$this->extendedPage->resetFrom
			);
		}

		return $form;
	}


	public function formSuccess()
	{
		if ($this->backlink) {
			$this->restoreRequest($this->backlink);
		}

		if ($this->extendedPage->page) {
			$this->redirect('this', array('route' => $this->extendedPage->page->mainRoute));
		}

		$this->redirect('this');
	}


	public function formError($control, $message)
	{
		$this->flashMessage($this->translator->translate($message), 'warning');
		$this->redirect('this');
	}


	public function renderDefault()
	{
		if ($this->user->isLoggedIn()) {
			$this->flashMessage($this->translator->translate('You are already logged in.'), 'info');
		}

		$this->template->loginProviders = $this->securityManager->getLoginProviders();
	}
}
