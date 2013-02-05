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
use CmsModule\Content\Forms\LayoutFormFactory;
use CmsModule\Content\Forms\LayouteditFormFactory;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 *
 * @secured
 */
class TemplatesPresenter extends BasePresenter
{

	/** @persistent */
	public $key;

	/** @var Venne\Module\TemplateManager */
	protected $templateManager;

	/** @var array */
	protected $_layouts;

	/** @var LayoutFormFactory */
	protected $layoutFormFactory;

	/** @var LayouteditFormFactory */
	protected $layouteditFormFactory;

	/** @var Venne\Module\Helpers */
	protected $moduleHelpers;


	/**
	 * @param \Venne\Module\TemplateManager $templateManager
	 */
	public function injectTemplateManager(Venne\Module\TemplateManager $templateManager)
	{
		$this->templateManager = $templateManager;
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
	 * @param \Venne\Module\Helpers $moduleHelpers
	 */
	public function injectModulesHelper(Venne\Module\Helpers $moduleHelpers)
	{
		$this->moduleHelpers = $moduleHelpers;
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
			$this->_layouts = array();

			foreach ($this->context->parameters['modules'] as $name => $item) {
				$this->_layouts[$name] = $this->templateManager->getLayouts($name);
			}
		}

		return $this->_layouts;
	}


	protected function createComponentForm()
	{
		$form = $this->layouteditFormFactory->invoke();
		$form->onSuccess[] = $this->formSuccess;
		return $form;
	}


	public function formSuccess($form)
	{
		$this->flashMessage('Layout has been added.', 'success');

		if (!$this->isAjax()) {
			$this->redirect('edit', array('key' => $form->data));
		}
		$this->invalidateControl('content');
		$this->payload->url = $this->link('edit', array('key' => $form->data));
		$this->setView('edit');
		$this->changeAction('edit');
		$this->key = $form->data;

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


	public function formeditSuccess($form)
	{
		$this->flashMessage('Layout has been saved.', 'success');

		if (!$this->isAjax()) {
			$this->redirect('edit', array('key' => $form->data));
		}
		$this->invalidateControl('content');
		$this->payload->url = $this->link('edit', array('key' => $form->data));
		$this->key = $form->data;

		if (!$this->isAjax()) {
			$this->redirect('this');
		}
	}


	public function handleDelete($key)
	{
		$path = $this->moduleHelpers->expandPath($key, 'Resources');

		unlink($path);

		if(substr($path, -14) === '/@layout.latte') {
			\Venne\Utils\File::rmdir(dirname($path), TRUE);

			$this->flashMessage('Layout has been removed.', 'success');
		}else{
			$this->flashMessage('Template has been removed.', 'success');
		}

		if (!$this->isAjax()) {
			$this->redirect('this', array('key' => NULL));
		}
		$this->invalidateControl('content');
		$this['panel']->invalidateControl('content');
		$this->payload->url = $this->link('this', array('key' => NULL));
	}


	public function renderDefault()
	{
		$this->template->templateManager = $this->templateManager;
	}
}
