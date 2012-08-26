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

use Venne;
use Nette\Application\UI;
use Nette\Security;
use Nette\Callback;

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



	public function startup()
	{
		parent::startup();

		if (!$this->context->createCheckConnection()) {
			$this->flashMessage("Only administrator can be logged", "warning");
		}
	}



	/**
	 * Sign in form component factory.
	 *
	 * @return Nette\Application\UI\Form
	 */
	protected function createComponentSignInForm($name)
	{
		$form = $this->form->invoke();
		return $form;
	}

}
