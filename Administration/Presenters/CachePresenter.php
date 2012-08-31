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
use Venne\Caching\CacheManager;
use Venne\Forms\Form;
use CmsModule\Forms\Rendering\BootstrapRenderer;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 *
 * @secured
 */
class CachePresenter extends BasePresenter
{

	/** @var CacheManager */
	protected $cacheManager;

	/** @var BootstrapFormRenderer */
	protected $renderer;


	public function injectCacheManager(CacheManager $cacheManager)
	{
		$this->cacheManager = $cacheManager;
	}


	public function injectRenderer(BootstrapRenderer $renderer)
	{
		$this->renderer = $renderer;
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
	public function handleClear()
	{
	}


	public function createComponentForm()
	{
		$form = new Form;
		$form->setRenderer($this->renderer);

		$form->addGroup('Setup');
		$form->addRadioList('section', 'Section', array('all' => 'All', 'namespace' => 'Namespace', 'sessions' => 'Sessions'))
			->setDefaultValue('all')
			->addCondition($form::EQUAL, 'namespace')->toggle('namespace');

		$form->addGroup('Namespace')->setOption('container', 'fieldset id=namespace');
		$form->addText('namespace');
		$form->addSubmit('_submit', 'Clear');

		$form->onSuccess[] = $this->processForm;

		return $form;
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
			} catch (\Nette\InvalidArgumentException $e) {
				$this->flashMessage($e->getMessage(), 'warning');
				return;
			}
		} elseif ($values['section'] === 'sessions') {
			$this->cacheManager->cleanSessions();
		}

		$this->flashMessage('Cache has been cleared.', 'success');

		if (!$this->isAjax()) {
			$this->redirect('this');
		}
	}
}
