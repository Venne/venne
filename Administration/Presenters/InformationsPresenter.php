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
class InformationsPresenter extends BasePresenter
{


	/** @persistent */
	public $id;

	/** @var Callback */
	protected $form;


	function __construct(Callback $form)
	{
		$this->form = $form;
	}


	public function createComponentWebsiteForm()
	{
		$form = $this->form->invoke();
		$form->setRoot("parameters.website");
		$form->onSuccess[] = function($form)
		{
			$form->getPresenter()->flashMessage("Website has been saved", "success");
			if (!$form->getPresenter()->isAjax()) {
				$form->getPresenter()->redirect("this");
			}
		};
		return $form;
	}


	public function createComponentModulesDefaultForm()
	{
		$form = $this->context->cms->createModulesDefaultForm();
		$form->setRoot("parameters.website");
		//$form->addSubmit("_submit", "Save");
		$form->onSuccess[] = function($form)
		{
			$form->getPresenter()->flashMessage("Changes has been saved", "success");
			$form->getPresenter()->redirect("this");
		};
		return $form;
	}

}
