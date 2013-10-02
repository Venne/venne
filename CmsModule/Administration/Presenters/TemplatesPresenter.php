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

use CmsModule\Content\Forms\LayouteditFormFactory;
use CmsModule\Content\Forms\LayoutFormFactory;
use CmsModule\Content\Forms\OverloadFormFactory;
use Venne\Forms\Form;
use Venne\Module\Helpers;
use Venne\Module\TemplateManager;
use Venne\Utils\File;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 *
 * @secured
 */
class TemplatesPresenter extends BasePresenter
{

	/** @persistent */
	public $key;

	/** @var TemplateManager */
	protected $templateManager;

	/** @var array */
	protected $_layouts;

	/** @var LayoutFormFactory */
	protected $layoutFormFactory;

	/** @var LayouteditFormFactory */
	protected $layouteditFormFactory;

	/** @var OverloadFormFactory */
	protected $overloadFormFactory;

	/** @var Helpers */
	protected $moduleHelpers;


	/**
	 * @param TemplateManager $templateManager
	 */
	public function injectTemplateManager(TemplateManager $templateManager)
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
	 * @param OverloadFormFactory $overloadFormFactory
	 */
	public function injectOverloadFormFactory(OverloadFormFactory $overloadFormFactory)
	{
		$this->overloadFormFactory = $overloadFormFactory;
	}


	/**
	 * @param Helpers $moduleHelpers
	 */
	public function injectModulesHelper(Helpers $moduleHelpers)
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
	 * @secured
	 */
	public function actionCreate()
	{
	}


	/**
	 * @secured
	 */
	public function actionEdit()
	{
	}


	/**
	 * @secured
	 */
	public function actionRemove()
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


	protected function createComponentOverloadForm()
	{
		$form = $this->overloadFormFactory->invoke();
		$form->onSuccess[] = $this->overloadFormSuccess;
		return $form;
	}


	public function overloadFormSuccess(Form $form)
	{
		if ($form->isSubmitted() === $form->getSaveButton() && !$form->errors) {
			$this->redirect('default');
		}
	}


	public function formSuccess($form)
	{
		$this->flashMessage($this->translator->translate('Layout has been added.'), 'success');

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
		$this->flashMessage($this->translator->translate('Layout has been saved.'), 'success');

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


	/**
	 * @secured(privilege="remove")
	 */
	public function handleDelete($key)
	{
		$path = $this->moduleHelpers->expandPath($key, 'Resources/layouts');

		unlink($path);

		if (substr($path, -14) === '/@layout.latte') {
			File::rmdir(dirname($path), TRUE);

			$this->flashMessage($this->translator->translate('Layout has been removed.'), 'success');
		} else {
			$this->flashMessage($this->translator->translate('Template has been removed.'), 'success');
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
