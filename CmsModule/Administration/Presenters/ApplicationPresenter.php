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

use CmsModule\Forms\SystemAccountFormFactory;
use CmsModule\Forms\SystemAdministrationFormFactory;
use CmsModule\Forms\SystemApplicationFormFactory;
use CmsModule\Forms\SystemDatabaseFormFactory;
use Nette\Callback;
use Venne\Forms\Form;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 *
 * @secured
 */
class ApplicationPresenter extends BasePresenter
{

	/** @var SystemAdministrationFormFactory */
	protected $systemForm;

	/** @var SystemApplicationFormFactory */
	protected $applicationForm;

	/** @var SystemDatabaseFormFactory */
	protected $databaseForm;

	/** @var SystemAccountFormFactory */
	protected $accountForm;


	public function injectApplicationForm(SystemApplicationFormFactory $applicationForm)
	{
		$this->applicationForm = $applicationForm;
	}


	public function injectDatabaseForm(SystemDatabaseFormFactory $databaseForm)
	{
		$this->databaseForm = $databaseForm;
	}


	public function injectAccountForm(SystemAccountFormFactory $accountForm)
	{
		$this->accountForm = $accountForm;
	}


	public function injectAdministrationForm(SystemAdministrationFormFactory $systemForm)
	{
		$this->systemForm = $systemForm;
	}


	/**
	 * @secured(privilege="system")
	 */
	public function actionDefault()
	{
	}


	/**
	 * @secured
	 */
	public function actionDatabase()
	{
	}


	/**
	 * @secured
	 */
	public function actionAccount()
	{
	}


	/**
	 * @secured
	 */
	public function actionAdmin()
	{
	}


	protected function createComponentSystemForm()
	{
		$form = $this->systemForm->invoke();
		return $form;
	}


	protected function createComponentApplicationForm()
	{
		$form = $this->applicationForm->invoke();
		$form->onSuccess[] = function (Form $form) {
			$form->getPresenter()->flashMessage("Application settings has been updated", "success");
			$form->getPresenter()->redirect("this");
		};
		return $form;
	}


	protected function createComponentDatabaseForm()
	{
		$form = $this->databaseForm->invoke();
		$form->onSuccess[] = function (Form $form) {
			$form->getPresenter()->flashMessage("Database settings has been updated", "success");
			$form->getPresenter()->redirect("this");
		};
		return $form;
	}


	protected function createComponentAccountForm()
	{
		$form = $this->accountForm->invoke();
		$form->onSuccess[] = function (Form $form) {
			$form->getPresenter()->flashMessage("Account settings has been updated", "success");
			$form->getPresenter()->redirect("this");
		};
		return $form;
	}
}
