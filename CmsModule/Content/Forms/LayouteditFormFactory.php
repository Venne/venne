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
use FormsModule\ControlExtensions\ControlExtension;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class LayouteditFormFactory extends FormFactory
{

	const TYPE_LAYOUT = 'layout';

	const TYPE_PRESENTER = 'presenter';

	const TYPE_COMPONENT = 'component';

	/** @var Venne\Module\TemplateManager */
	protected $templateManager;

	/** @var string */
	protected $appDir;

	/** @var array */
	protected $modules;

	/** @var Helpers */
	protected $moduleHelpers;

	/** @var array */
	protected $types = array(
		self::TYPE_LAYOUT => 'Layout',
		self::TYPE_PRESENTER => 'Presenter',
		self::TYPE_COMPONENT => 'Component',
	);


	/**
	 * @param $appDir
	 * @param $modules
	 */
	public function __construct(Venne\Module\TemplateManager $templateManager, $appDir, $modules, Helpers $moduleHelpers)
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
			->addCondition($form::EQUAL, self::TYPE_LAYOUT)->toggle('form-layoutName')
			->endCondition()->addCondition($form::EQUAL, self::TYPE_PRESENTER)->toggle('form-presenter')
			->endCondition()->addCondition($form::EQUAL, self::TYPE_COMPONENT)->toggle('form-component')
			->endCondition()->addCondition($form::EQUAL, array(self::TYPE_PRESENTER, self::TYPE_COMPONENT))->toggle('form-layout');

		$form->addGroup()->setOption('id', 'form-layoutName');
		$form->addText('layoutName', 'Layout');

		$form->addGroup()->setOption('id', 'form-layout');
		$form->addSelect('layout', 'Layout');

		$form->addGroup()->setOption('id', 'form-presenter');
		$form->addText('presenter', 'Presenter');
		$form->addText('action', 'Action');

		$form->addGroup()->setOption('id', 'form-component');
		$form->addText('component', 'Component');

		$form->addGroup('Template')->setOption('class', 'full');
		$form->addTextArea('text', NULL, NULL, 30)
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

		if ($form->data && ($type === self::TYPE_PRESENTER || $type === self::TYPE_COMPONENT)) {
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
			} elseif ($type === self::TYPE_PRESENTER) {
				$form['layout']->setDefaultValue($this->getLayoutByKey($form->data));
				$form['presenter']->setDefaultValue($this->getPresenterByKey($form->data));
				$form['action']->setDefaultValue($this->getActionByKey($form->data));
			} elseif ($type === self::TYPE_COMPONENT) {
				$form['layout']->setDefaultValue($this->getLayoutByKey($form->data));
				$form['component']->setDefaultValue($this->getComponentByKey($form->data));
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
		} else if ($values['type'] === self::TYPE_COMPONENT) {
			$key .= ($values['layout'] ? '/' . $values['layout'] : '') . "/{$values['component']}Control.latte";
		} else if ($values['type'] === self::TYPE_PRESENTER) {
			$p = str_replace(':', '.', $values['presenter']);
			$key .= ($values['layout'] ? '/' . $values['layout'] : '') . "/{$p}.{$values['action']}.latte";
		}

		return $key;
	}


	protected function getLayoutPathBy($module, $name)
	{
		return ($module === 'app' ? $this->appDir : $this->modules[$module]['path']) . '/layouts/' . $name;
	}


	public function getLayoutPathByKey($key)
	{
		$key2 = explode('/', $key, 2);
		$module = substr($key2[0], 1, -6);
		$path = $this->getLayoutPathBy($module, $key2[1]);
		return ($this->getTypeByKey($key) === self::TYPE_LAYOUT ? $path . '/@layout.latte' : $path);
	}


	protected function getLayoutByKey($key)
	{
		$key = explode('/', $key);
		return count($key > 2) ? $key[count($key) - 2] : NULL;
	}


	protected function getComponentByKey($key)
	{
		$key = explode('/', $key);
		return lcfirst(substr($key[count($key) - 1], 0, -13));
	}


	protected function getNameByKey($key)
	{
		$key = explode('/', $key);
		return $key[count($key) - 1];
	}


	protected function getModuleByKey($key)
	{
		$key = explode('/', $key);
		return substr($key[0], 1, -6);
	}


	protected function getPresenterByKey($key)
	{
		$key = substr($key, strrpos($key, '/') + 1);
		$key = explode('.', $key);
		unset($key[count($key) - 1]);
		unset($key[count($key) - 1]);

		return implode(':', $key);
	}


	protected function getActionByKey($key)
	{
		$key = substr($key, strrpos($key, '/') + 1);
		$key = explode('.', $key);
		return lcfirst($key[count($key) - 2]);
	}


	protected function getTypeByKey($key)
	{
		$key = explode('/', $key);

		if (substr($key[count($key) - 1], -13) === 'Control.latte') {
			return self::TYPE_COMPONENT;
		}

		if ($key[count($key) - 1] === '@layout.latte') {
			return self::TYPE_LAYOUT;
		}

		return self::TYPE_PRESENTER;
	}
}
