<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace CmsModule\Administration\Presenters;

use Nette\Callback;
use Venne\Forms\Form;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class LoginPresenter extends BasePresenter
{

	/** @persistent */
	public $backlink;

	/** @var Callback */
	protected $form;


	public function __construct($form)
	{
		parent::__construct();

		$this->form = $form;
	}


	protected function startup()
	{
		parent::startup();

		if (!$this->context->createCheckConnection()) {
			$this->flashMessage($this->translator->translate('Only administrator can be logged'), 'warning');
		}

		if ($this->user->isLoggedIn()) {
			$this->redirect(':Cms:Admin:' . $this->context->parameters['administration']['defaultPresenter'] . ':');
		}
	}


	/**
	 * Sign in form component factory.
	 *
	 * @return Form
	 */
	protected function createComponentSignInForm()
	{
		$form = $this->form->invoke();
		return $form;
	}
}
