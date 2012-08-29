<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace CmsModule\Content\Forms;

use Venne;
use Venne\Forms\FormFactory;
use Venne\Forms\Form;
use CmsModule\Services\ScannerService;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class LayoutFormFactory extends FormFactory
{

	/** @var string */
	protected $appDir;

	/** @var array */
	protected $modules;

	/** @var ScannerService */
	protected $scannerService;


	/**
	 * @param ScannerService $scannerService
	 * @param $appDir
	 * @param $modules
	 */
	public function __construct(ScannerService $scannerService, $appDir, $modules)
	{
		$this->scannerService = $scannerService;
		$this->appDir = $appDir;
		$this->modules = $modules;
	}


	/**
	 * @param Form $form
	 */
	protected function configure(Form $form)
	{
		$layouts = array_keys($this->scannerService->getLayoutFiles());
		if (array_search('app', $layouts) === false) {
			$layouts = array_merge(array('app'), $layouts);
		}

		$form->addGroup('Layout settings');
		$form->addText('name', 'Name');
		$form->addSelect('parent', 'Save to')->setItems($layouts, FALSE);

		$form->addSubmit('_submit', 'Save');
	}


	public function handleSuccess(Form $form)
	{
		$values = $form->getValues();
		$path = $this->getLayoutPathBy($values['parent'], $values['name']);

		umask(0000);
		@mkdir($path, 0777, true);

		file_put_contents($path . '/@layout.latte', '');
	}


	protected function getLayoutPathBy($module, $name)
	{
		return ($module === 'app' ? $this->appDir : $this->modules[$module]['path']) . '/layouts/' . $name;
	}


	protected function getLayoutPathByKey($key)
	{
		$module = substr($key, 1, strpos($key, '/') - 1);
		$name = substr($key, strrpos($key, '/') + 1);
		return $this->getLayoutPathBy($module, $name);
	}
}
