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

use Venne\Application\PresenterFactory;
use Venne\Forms\Form;
use Venne\Forms\FormFactory;
use Venne\Module\Helpers;
use Venne\Module\TemplateManager;
use Venne\Widget\WidgetManager;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class OverloadFormFactory extends FormFactory
{

	/** @var WidgetManager */
	private $widgetManager;

	/** @var TemplateManager */
	private $templateManager;

	/** @var Helpers */
	private $moduleHelpers;

	/** @var PresenterFactory */
	private $presenterFactory;

	/** @var array */
	private $modules;


	/**
	 * @param $modules
	 * @param WidgetManager $widgetManager
	 * @param TemplateManager $templateManager
	 * @param Helpers $moduleHelpers
	 * @param PresenterFactory $presenterFactory
	 */
	public function __construct($modules, WidgetManager $widgetManager, TemplateManager $templateManager, Helpers $moduleHelpers, PresenterFactory $presenterFactory)
	{
		$this->modules = $modules;
		$this->widgetManager = $widgetManager;
		$this->templateManager = $templateManager;
		$this->moduleHelpers = $moduleHelpers;
		$this->presenterFactory = $presenterFactory;
	}


	/**
	 * @param Form $form
	 */
	protected function configure(Form $form)
	{
		$form->addGroup();
		$form->addSelect('type', 'Type')->setItems(array(
			'presenter', 'component'
		), FALSE)
			->addCondition($form::EQUAL, 'presenter')->toggle($form->name . '-presenter')
			->endCondition()
			->addCondition($form::EQUAL, 'component')->toggle($form->name . '-component');

		$form->addGroup()->setOption('id', $form->name . '-presenter');
		$form->addSelect('presenter', 'Presenter');

		$form->addGroup()->setOption('id', $form->name . '-component');
		$form->addSelect('component', 'Component');

		$form->addGroup('Target');
		$form->addSelect('target', 'Target module')
			->setTranslator(NULL)
			->setItems(array_keys($this->modules), FALSE)
			->getControlPrototype()->onChange = 'this.form.submit();';

		$form->addSelect('layout', 'Target layout');

		$form->addSaveButton('Save');
	}


	public function handleAttached(Form $form)
	{
		$presenters = array();
		foreach ($form->presenter->context->findByTag('presenter') as $name => $item) {
			if (substr($name, -7) === 'Factory') {
				$name = substr($name, 0, -7);
			}
			$presenter = $this->presenterFactory->formatPresenterFromServiceName($name);
			$class = $this->presenterFactory->getPresenterClass($presenter);

			if (!is_subclass_of($class, '\CmsModule\Content\Presenters\PagePresenter')) {
				continue;
			}

			$name = substr($name, 0, -9);
			$name = explode('.', $name);

			foreach ($name as &$n) {
				$n = ucfirst($n);
			}

			$presenters[$class] = implode(':', $name);
		}

		$components = array();
		foreach ($this->widgetManager->getWidgets() as $name => $factory) {
			$components[$factory['class']] = ucfirst($name) . 'Control';
		}

		$form['presenter']->setItems($presenters);
		$form['component']->setItems($components);

		$module = $form['target']->value ? $form['target']->value : key($this->modules);
		$form['layout']->setItems(array_keys($this->templateManager->getLayoutsByModule($module)), FALSE)
			->setDisabled(FALSE)
			->setPrompt('-- All --');
	}


	public function handleSuccess(Form $form)
	{
		if ($form->isSubmitted() === $form->getSaveButton()) {
			$values = $form->values;

			if ($values['type'] == 'component') {
				$file = $this->getFileByClass($values['component']);
				$baseName = $file->getBasename('.php') . '.latte';
				$template = $file->getPath() . '/' . $baseName;

				$target = $this->moduleHelpers->expandPath('@' . $values['target'] . 'Module', 'Resources/layouts');
				if ($values['layout']) {
					$target .= '/' . $values['layout'];
				}
				$target .= '/' . $baseName;
			} else {
				$file = $this->getFileByClass($values['presenter']);
				$baseName = $file->getBasename('Presenter.php');
				$template = $file->getPath() . '/templates/' . $baseName . '/default.latte';

				$target = $this->moduleHelpers->expandPath('@' . $values['target'] . 'Module', 'Resources/layouts');
				if ($values['layout']) {
					$target .= '/' . $values['layout'];
				}
				$target .= '/' . str_replace(':', '.', $this->presenterFactory->unformatPresenterClass($values['presenter'])) . '.default.latte';
			}

			if (!copy($template, $target)) {
				$form->addError("Unable to copy file '$template' to '$target'.");
			}
		}
	}


	/**
	 * @param $class
	 * @return \SplFileInfo
	 */
	private function getFileByClass($class)
	{
		$reflector = new \ReflectionClass($class);
		return new \SplFileInfo($reflector->getFileName());
	}
}
