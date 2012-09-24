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
use CmsModule\Forms\WebsiteFormFactory;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 *
 * @secured
 */
class InformationsPresenter extends BasePresenter
{


	/** @var WebsiteFormFactory */
	protected $form;


	public function injectForm(WebsiteFormFactory $form)
	{
		$this->form = $form;
	}


	/**
	 * @secured(privilege="show")
	 */
	public function actionDefault()
	{
	}


	public function createComponentWebsiteForm()
	{
		$presenter = $this;

		$form = $this->form->createForm();
		$form->onSuccess[] = $this->formSuccess;
		return $form;
	}


	public function formSuccess()
	{
		$this->flashMessage('Website has been saved', 'success');

		if (!$this->isAjax()) {
			$this->redirect('this');
		}
		$this->invalidateControl('content');
	}
}
