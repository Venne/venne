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


/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class LayouteditFormFactory extends FormFactory
{


	/** @var string */
	protected $appDir;

	/** @var array */
	protected $modules;


	/**
	 * @param $appDir
	 * @param $modules
	 */
	public function __construct($appDir, $modules)
	{
		$this->appDir = $appDir;
		$this->modules = $modules;
	}


	/**
	 * @param Form $form
	 */
	protected function configure(Form $form)
	{
		$form->addGroup();
		$form->addTextArea('text', NULL, 500, 40)->getControlPrototype()->attrs['class'] = 'input-xxlarge';

		$form->addSubmit('_submit', 'Save');
	}


	public function handleLoad(Form $form)
	{
		$form['text']->setDefaultValue(file_get_contents($this->getLayoutPathByKey($form->data) . '/@layout.latte'));
	}


	public function handleSuccess(Form $form)
	{
		$values = $form->getValues();
		$path = $this->getLayoutPathByKey($form->data);

		file_put_contents($path . '/@layout.latte', $values['text']);
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
