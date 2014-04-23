<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\System\AdminModule;

use Nette\Application\UI\Form;
use Nette\Application\UI\Presenter;
use Venne\System\AdminPresenterTrait;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 *
 * @secured
 */
class CachePresenter extends Presenter
{

	use AdminPresenterTrait;

	/** @var CacheFormFactory */
	private $cacheFormFactory;


	/**
	 * @param CacheFormFactory $cacheFormFactory
	 */
	public function inject(CacheFormFactory $cacheFormFactory)
	{
		$this->cacheFormFactory = $cacheFormFactory;
	}


	/**
	 * @secured(privilege="show")
	 */
	public function actionDefault()
	{
	}


	public function formSuccess()
	{
		$this->flashMessage($this->translator->translate('Cache has been cleared.'), 'success');

		if (!$this->isAjax()) {
			$this->redirect('this');
		}
	}


	protected function createComponentForm()
	{
		$form = $this->cacheFormFactory->create();
		$form->onSuccess[] = $this->formSuccess;
		return $form;
	}
}
