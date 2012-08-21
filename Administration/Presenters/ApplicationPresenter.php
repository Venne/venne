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
use Nette\Callback;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 *
 * @secured
 */
class ApplicationPresenter extends BasePresenter
{


	/** @persistent */
	public $key;

	/** @var Callback */
	protected $systemForm;

	/** @var Callback */
	protected $applicationForm;

	/** @var Callback */
	protected $databaseForm;

	/** @var Callback */
	protected $accountForm;



	function __construct($systemForm, $applicationForm, $databaseForm, $accountForm)
	{
		$this->systemForm = $systemForm;
		$this->applicationForm = $applicationForm;
		$this->databaseForm = $databaseForm;
		$this->accountForm = $accountForm;
	}



	public function createComponentSystemForm()
	{
		$form = $this->systemForm->invoke();
		$form->onSuccess[] = $this->systemFormSuccess;
		return $form;
	}



	public function systemFormSuccess(\CmsModule\Forms\SystemForm $form)
	{
		$this->absoluteUrls = true;
		$url = $this->getHttpRequest()->getUrl();

		$path = "{$url->scheme}://{$url->host}{$url->scriptPath}";

		$oldPath = $path . $this->context->parameters['administration']['routePrefix'];
		$newPath = $path . $form['administration']['routePrefix']->getValue();

		if($form['administration']['routePrefix']->getValue() == ''){
			$oldPath .= '/';
		}

		if($this->context->parameters['administration']['routePrefix'] == ''){
			$newPath .= '/';
		}

		$form->getPresenter()->flashMessage("Administration settings has been updated", "success");
		$form->getPresenter()->redirectUrl(str_replace($oldPath, $newPath, $this->link('this')));
	}



	public function createComponentApplicationForm()
	{
		$form = $this->applicationForm->invoke();
		$form->onSuccess[] = function($form) {
					$form->getPresenter()->flashMessage("Application settings has been updated", "success");
					$form->getPresenter()->redirect("this");
				};
		return $form;
	}



	public function createComponentDatabaseForm()
	{
		$form = $this->databaseForm->invoke();
		$form->onSuccess[] = function($form) {
					$form->getPresenter()->flashMessage("Database settings has been updated", "success");
					$form->getPresenter()->redirect("this");
				};
		return $form;
	}



	public function createComponentAccountForm()
	{
		$form = $this->accountForm->invoke();
		$form->onSuccess[] = function($form) {
					$form->getPresenter()->flashMessage("Account settings has been updated", "success");
					$form->getPresenter()->redirect("this");
				};
		return $form;
	}

}
