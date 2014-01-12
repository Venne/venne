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

use FormsModule\ControlExtensions\ControlExtension;
use Venne\Forms\Form;
use Venne\Forms\FormFactory;
use Venne\Module\Helpers;
use Venne\Module\TemplateManager;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class LayouteditFormFactory extends FormFactory
{

	const TYPE_LAYOUT = 'layout';

	const TYPE_TEMPLATE = 'template';

	/** @var TemplateManager */
	protected $templateManager;

	/** @var string */
	protected $appDir;

	/** @var array */
	protected $modules;

	/** @var Helpers */
	protected $moduleHelpers;

	/** @var array */
	protected $types = array(
		self::TYPE_LAYOUT => self::TYPE_LAYOUT,
		self::TYPE_TEMPLATE => self::TYPE_TEMPLATE,
	);


	/**
	 * @param TemplateManager $templateManager
	 * @param $appDir
	 * @param $modules
	 * @param Helpers $moduleHelpers
	 */
	public function __construct(TemplateManager $templateManager, $appDir, $modules, Helpers $moduleHelpers)
	{
		$this->templateManager = $templateManager;
		$this->appDir = $appDir;
		$this->modules = $modules;
		$this->moduleHelpers = $moduleHelpers;
	}


	protected function getControlExtensions()
	{
		return array_merge(parent::getControlExtensions(), array(
			new ControlExtension(),
		));
	}


	/**
	 * @param Form $form
	 */
	protected function configure(Form $form)
	{
		$form->addGroup();

		$form->addSelect('target', 'Target module')
			->setTranslator(NULL)
			->setItems(array_keys($this->modules), FALSE);
		$form->addSelect('type', 'Type', $this->types)
			->addCondition($form::EQUAL, self::TYPE_LAYOUT)->toggle('form-layout')
			->endCondition()->addCondition($form::EQUAL, self::TYPE_TEMPLATE)->toggle('form-template');

		$form->addGroup()->setOption('id', 'form-layout');
		$form->addText('layoutName', 'Layout');

		$form->addGroup()->setOption('id', 'form-template');
		$form->addSelect('layout', 'Layout');
		$form->addText('template', 'Template');

		$form->addGroup('Template')->setOption('class', 'full');
		$form->addCode('text', NULL, NULL, 30)
			->getControlPrototype()->attrs['class'] = 'input-block-level';

		$form->addSubmit('_submit', 'Save');
	}


	public function handleAttached(Form $form)
	{
		$path = $this->moduleHelpers->expandPath($form->data, 'Resources/layouts');
		$type = $this->getTypeByKey($form->data);

		if ($path && !is_writable($path)) {
			$form->addError("File '$path' is not writable.");
		}

		if ($form->data && $type === self::TYPE_TEMPLATE) {
			$module = $this->getModuleByKey($form->data);

			$form['layout']->setItems(array_keys($this->templateManager->getLayoutsByModule($module)), FALSE)->setPrompt('-- All --');
		}
	}


	public function handleLoad(Form $form)
	{
		if ($form->data) {

			$data = trim(file_get_contents($this->moduleHelpers->expandPath($form->data, 'Resources/layouts')));

			$type = $this->getTypeByKey($form->data);
			$module = $this->getModuleByKey($form->data);

			$form['type']->setDefaultValue($type);
			$form['target']->setDefaultValue($module);

			if ($type === self::TYPE_LAYOUT) {
				$form['layoutName']->setDefaultValue($this->getLayoutByKey($form->data));
			} elseif ($type === self::TYPE_TEMPLATE) {
				$form['layout']->setDefaultValue($this->getLayoutByKey($form->data));
				$form['template']->setDefaultValue($this->getTemplateByKey($form->data));
			}

			$form['text']->setDefaultValue($data);
		}
	}


	public function handleSuccess(Form $form)
	{
		$values = $form->getValues();
		$oldPath = $this->moduleHelpers->expandPath($form->data, 'Resources/layouts');
		$form->data = $this->getKeyByValues($values);

		$path = $this->moduleHelpers->expandPath($form->data, 'Resources/layouts');

		umask(0000);
		if (!file_exists(dirname($path))) {
			if (!@mkdir(dirname($path), 0777, TRUE)) {
				$form->addError("File '$path' is not writable.");
			}
		}

		file_put_contents($path, $values['text']);

		if ($oldPath && $oldPath !== $path) {
			@unlink($oldPath);
		}
	}


	protected function getKeyByValues($values)
	{
		$key = "@{$values['target']}Module";

		if ($values['type'] === self::TYPE_LAYOUT) {
			$key .= "/{$values['layoutName']}/@layout.latte";
		} else if ($values['type'] === self::TYPE_TEMPLATE) {
			$key .= ($values['layout'] ? '/' . $values['layout'] : '') . "/{$values['template']}.latte";
		}

		return $key;
	}


	protected function getLayoutByKey($key)
	{
		$key = explode('/', $key);
		return count($key > 2) ? $key[count($key) - 2] : NULL;
	}


	protected function getModuleByKey($key)
	{
		$key = explode('/', $key);
		return substr($key[0], 1, -6);
	}


	protected function getTemplateByKey($key)
	{
		$key = explode('/', $key);
		return substr($key[count($key) - 1], 0, -6);
	}


	protected function getTypeByKey($key)
	{
		$key = explode('/', $key);

		if ($key[count($key) - 1] === '@layout.latte') {
			return self::TYPE_LAYOUT;
		}

		return self::TYPE_TEMPLATE;
	}

}
