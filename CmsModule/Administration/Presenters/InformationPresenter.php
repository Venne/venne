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

use CmsModule\Forms\WebsiteFormFactory;
use Nette\Application\ForbiddenRequestException;
use Nette\Callback;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 *
 * @secured
 */
class InformationPresenter extends BasePresenter
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


	/**
	 * @secured
	 */
	public function actionEdit()
	{
	}


	protected function createComponentWebsiteForm()
	{
		$form = $this->form->createForm();
		$form->onSuccess[] = $this->formSuccess;

		// permissions
		if (!$this->isAuthorized('edit')) {
			$form->onAttached[] = function ($form) {
				if ($form->isSubmitted()) {
					throw new ForbiddenRequestException;
				}
			};

			foreach ($form->getComponents(TRUE) as $component) {
				$component->setDisabled(TRUE);
			}
		}

		return $form;
	}


	public function formSuccess()
	{
		$this->flashMessage($this->translator->translate('Website has been saved'), 'success');

		if (!$this->isAjax()) {
			$this->redirect('this');
		}
		$this->invalidateControl('content');
	}
}
