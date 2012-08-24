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
use CmsModule\Content\Forms\LayoutForm;
use CmsModule\Content\Forms\LayouteditForm;
use Nette\Callback;

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

	/** @var Callback */
	protected $layoutFormFactory;

	/** @var Callback */
	protected $layouteditFormFactory;


	/**
	 * @param ScannerService $scannerService
	 */
	public function __construct(ScannerService $scannerService, Callback $layoutFormFactory, Callback $layouteditFormFactory)
	{
		parent::__construct();

		$this->scannerService = $scannerService;
		$this->layoutFormFactory = $layoutFormFactory;
		$this->layouteditFormFactory = $layouteditFormFactory;
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
		/** @var $form LayoutForm */
		$form = $this->layoutFormFactory->invoke();
		$form->onSuccess[] = $this->formSuccess;
		return $form;
	}


	public function formSuccess(LayoutForm $form)
	{
		$values = $form->getValues();
		$path = $this->getLayoutPathBy($values['parent'], $values['name']);

		umask(0000);
		@mkdir($path, 0777, true);

		file_put_contents($path . '/@layout.latte', '');

		$this->flashMessage('Layout has been added.', 'success');

		if (!$this->isAjax()) {
			$this->redirect('edit', array('key' => "@{$values['parent']}/{$values['name']}"));
		}
		$this['panel']->invalidateControl('content');
		$this->payload->url = $this->link('edit', array('key' => "@{$values['parent']}/{$values['name']}"));
		$this->setView('edit');
		$this->changeAction('edit');
		$this->key = "@{$values['parent']}/{$values['name']}";
	}


	protected function createComponentFormedit()
	{
		/** @var $form LayoutForm */
		$form = $this->layouteditFormFactory->invoke();
		$form->onSuccess[] = $this->formeditSuccess;
		return $form;
	}


	public function formeditSuccess(LayouteditForm $form)
	{
		$values = $form->getValues();
		$path = $this->getLayoutPathByKey($this->key);

		file_put_contents($path . '/@layout.latte', $values['text']);

		$this->flashMessage('Layout has been saved.', 'success');

		if (!$this->isAjax()) {
			$this->redirect('this');
		}
	}


	protected function getLayoutPathBy($module, $name)
	{
		return ($module === 'app' ? $this->context->parameters['appDir'] : $this->context->parameters['modules'][$module]['path']) . '/layouts/' . $name;
	}


	protected function getLayoutPathByKey($key)
	{
		$module = substr($key, 1, strpos($key, '/') - 1);
		$name = substr($key, strrpos($key, '/') + 1);
		return $this->getLayoutPathBy($module, $name);
	}


	public function handleDelete($key)
	{
		$path = $this->getLayoutPathByKey($key);

		\Venne\Utils\File::rmdir($path, true);

		$this->flashMessage('Layout has been removed.', 'success');

		if (!$this->isAjax()) {
			$this->redirect('default');
		}
		$this->invalidateControl('content');
	}


	public function renderDefault()
	{
		$this->template->layouts = $this->getScannedLayouts();
	}


	public function renderEdit()
	{
		$this['formedit']['text']->setDefaultValue(file_get_contents($this->getLayoutPathByKey($this->key) . '/@layout.latte'));
	}
}
