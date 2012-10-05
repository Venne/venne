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
use CmsModule\Services\ScannerService;
use CmsModule\Content\Forms\LayoutFormFactory;
use CmsModule\Content\Forms\LayouteditFormFactory;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 *
 * @secured
 */
class LayoutsPresenter extends BasePresenter
{

	/** @persistent */
	public $key;

	/** @var ScannerService */
	protected $scannerService;

	/** @var array */
	protected $_layouts;

	/** @var LayoutFormFactory */
	protected $layoutFormFactory;

	/** @var LayouteditFormFactory */
	protected $layouteditFormFactory;


	/**
	 * @param ScannerService $scannerService
	 */
	public function injectScannerService(ScannerService $scannerService)
	{
		$this->scannerService = $scannerService;
	}


	public function injectLayoutForm(LayoutFormFactory $layoutForm)
	{
		$this->layoutFormFactory = $layoutForm;
	}


	public function injectLayouteditForm(LayouteditFormFactory $layouteditForm)
	{
		$this->layouteditFormFactory = $layouteditForm;
	}


	/**
	 * @secured(privilege="show")
	 */
	public function actionDefault()
	{
	}


	/**
	 * @secured(privilege="create")
	 */
	public function actionCreate()
	{
	}


	/**
	 * @secured(privilege="edit")
	 */
	public function actionEdit()
	{
	}


	protected function getScannedLayouts()
	{
		if ($this->_layouts === NULL) {
			$this->_layouts = $this->scannerService->getLayoutFiles();
		}

		return $this->_layouts;
	}


	protected function createComponentForm()
	{
		$form = $this->layoutFormFactory->invoke();
		$form->onSuccess[] = $this->formSuccess;
		return $form;
	}


	public function formSuccess($form)
	{
		$this->flashMessage('Layout has been added.', 'success');

		$values = $form->getValues();
		if (!$this->isAjax()) {
			$this->redirect('edit', array('key' => "@{$values['parent']}/{$values['name']}"));
		}
		$this->invalidateControl('content');
		$this->payload->url = $this->link('edit', array('key' => "@{$values['parent']}/{$values['name']}"));
		$this->setView('edit');
		$this->changeAction('edit');
		$this->key = "@{$values['parent']}/{$values['name']}";

		// refresh left panel
		$this['panel']->invalidateControl('content');
	}


	protected function createComponentFormedit()
	{
		$form = $this->layouteditFormFactory->invoke();
		$form->setData($this->key);
		$form->onSuccess[] = $this->formeditSuccess;
		return $form;
	}


	public function formeditSuccess()
	{
		$this->flashMessage('Layout has been saved.', 'success');

		if (!$this->isAjax()) {
			$this->redirect('this');
		}
	}


	public function handleDelete($key)
	{
		$path = $this->layouteditFormFactory->getLayoutPathByKey($key);

		\Venne\Utils\File::rmdir($path, true);

		$this->flashMessage('Layout has been removed.', 'success');

		if (!$this->isAjax()) {
			$this->redirect('this', array('key' => NULL));
		}
		$this->invalidateControl('content');
		$this['panel']->invalidateControl('content');
		$this->payload->url = $this->link('this', array('key' => NULL));
	}


	public function renderDefault()
	{
		$this->template->layouts = $this->getScannedLayouts();
	}
}
