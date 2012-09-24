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
use Venne\Module\Helpers;
use CmsModule\Services\ScannerService;


/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class LayouteditFormFactory extends FormFactory
{

	/** @var ScannerService */
	protected $scannerService;

	/** @var string */
	protected $appDir;

	/** @var array */
	protected $modules;


	/**
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
		$form->addGroup();
		$form->addSelect('layout', 'Parent layout', $this->scannerService->getLayoutFiles())->setPrompt('-------');
		$form->addTextArea('text', NULL, 500, 20)->getControlPrototype()->attrs['class'] = 'input-xxlarge';

		$form->addSubmit('_submit', 'Save');
	}


	public function handleLoad(Form $form)
	{
		$blocks = array();

		$data = trim(file_get_contents($this->getLayoutPathByKey($form->data) . '/@layout.latte'));
		if(substr($data, 0, 9) === '{extends ') {
			$start = strpos($data, ' ') + 1;
			$path = substr($data, $start, strpos($data, '}') - $start);

			$module = substr($path, 0, strpos($path, 'Module'));
			$name = explode('/', $path);
			$name = $name[2];

			$form['layout']->setDefaultValue("$module/$name");
			$data = substr($data, strpos($data, '}') + 1);

			$modules = $this->modules;
			$modules['app'] = array('path'=>$this->appDir);

			$realPath = Helpers::expandPath($path, $modules);

			$parent = trim(file_get_contents($realPath));
			preg_match_all("/\{block (.*)\}/U", $parent, $match, PREG_PATTERN_ORDER);
			foreach($match[1] as $item) {
				$blocks[] = substr($item, 0, 1) === '#' ? substr($item, 1) : $item;
			}
		}


		$form['text']->setDefaultValue($data);
	}





	public function handleSuccess(Form $form)
	{
		$values = $form->getValues();

		if($values['layout']) {
			dump($values['layout']);
			$data = explode('/', $values['layout']);
			$data = '{extends '.$data[0].'Module/layouts/'.$data[1].'/@layout.latte}' . $values['text'];
		} else {
			$data = $values['text'];
		}

		$path = $this->getLayoutPathByKey($form->data);

		file_put_contents($path . '/@layout.latte', $data);
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
