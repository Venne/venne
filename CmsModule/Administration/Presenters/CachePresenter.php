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

use Kdyby\Extension\Forms\BootstrapRenderer\BootstrapRenderer;
use Nette\Application\ForbiddenRequestException;
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

	/** @var BootstrapRenderer */
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
	public function actionEdit()
	{
	}


	protected function createComponentForm()
	{
		$form = new Form;
		$form->setRenderer($this->renderer);

		$form->addGroup('Setup');
		$form->addRadioList('section', 'Section', array('all' => 'All', 'namespace' => 'Namespace', 'sessions' => 'Sessions'))
			->setDefaultValue('all')
			->addCondition($form::EQUAL, 'namespace')->toggle('namespace');

		$form->addGroup('Namespace')->setOption('id', 'namespace');
		$form->addText('namespace');

		$form->addGroup();
		$form->addSaveButton('Clear');

		$form->onSuccess[] = $this->processForm;

		// permissions
		if (!$this->isAuthorized('edit')) {
			$form->onAttached[] = function (Form $form) {
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

		$this->flashMessage('Cache has been cleared.', 'success');

		if (!$this->isAjax()) {
			$this->redirect('this');
		}
	}
}
