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

use CmsModule\Forms\CacheFormFactory;
use Nette\InvalidArgumentException;
use Venne\Caching\CacheManager;
use Venne\Forms\Form;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 *
 * @secured
 */
class CachePresenter extends BasePresenter
{

	/** @var CacheManager */
	protected $cacheManager;

	/** @var CacheFormFactory */
	protected $cacheFormFactory;


	public function injectCacheManager(CacheManager $cacheManager)
	{
		$this->cacheManager = $cacheManager;
	}


	/**
	 * @param CacheFormFactory $cacheFormFactory
	 */
	public function injectCacheFormFactory(CacheFormFactory $cacheFormFactory)
	{
		$this->cacheFormFactory = $cacheFormFactory;
	}


	/**
	 * @secured(privilege="show")
	 */
	public function actionDefault()
	{
	}


	/**
	 * @secured(privilege="edit")
	 */
	public function actionEdit()
	{
	}


	public function processForm(Form $form)
	{
		$this->tryCall('handleClear', array());

		$values = $form->getValues();

		if ($values['section'] === 'all') {
			$this->cacheManager->clean();
		} elseif ($values['section'] === 'namespace') {
			try {
				$this->cacheManager->cleanNamespace($values['namespace']);
			} catch (InvalidArgumentException $e) {
				$this->flashMessage($e->getMessage(), 'warning');
				return;
			}
		} elseif ($values['section'] === 'sessions') {
			$this->cacheManager->cleanSessions();
		}

		$this->flashMessage($this->translator->translate('Cache has been cleared.'), 'success');

		if (!$this->isAjax()) {
			$this->redirect('this');
		}
	}


	protected function createComponentForm()
	{
		$form = $this->cacheFormFactory->invoke();
		$form->onSuccess[] = $this->processForm;
		return $form;
	}
}
