<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace CmsModule\Content\Presenters;

use Venne;
use Nette\Forms\Form;
use CmsModule\Forms\LoginFormFactory;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class LoginPresenter extends PagePresenter
{

	/** @var LoginFormFactory */
	protected $loginFormFactory;


	/**
	 * @param LoginFormFactory $loginFormFactory
	 */
	public function injectLoginFormFactory(LoginFormFactory $loginFormFactory)
	{
		$this->loginFormFactory = $loginFormFactory;
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
		if ($this->page->page) {
			$this->redirect('this', array('route' => $this->page->page->mainRoute));
		}
		$this->redirect('this');
	}
}
