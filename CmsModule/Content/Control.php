<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace CmsModule\Content;

use Nette\Localization\ITranslator;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 *
 * @property-read ITranslator $translator
 * @property-read string $templateName
 */
abstract class Control extends \Venne\Application\UI\Control
{

	/** @var array */
	private $configZal = array();

	/** @var string */
	private $variant;

	/** @var array */
	private $templateFiles = array();


	/**
	 * @return ITranslator
	 */
	public function getTranslator()
	{
		return $this->presenter->translator;
	}


	/**
	 * @return array
	 */
	protected function getTemplateNames()
	{
		return array(
			ucfirst($this->getUniqueId()) . 'Control',
			ucfirst($this->getName()) . 'Control',
		);
	}


	/**
	 * Formats component template files
	 *
	 * @param string
	 * @return array
	 */
	protected function formatTemplateFiles()
	{
		$list = parent::formatTemplateFiles();
		if ($this->variant) {
			$refl = $this->getReflection();
			$list = array_merge(array(
				dirname($refl->getFileName()) . '/' . $refl->getShortName() . '.' . $this->variant . '.latte',
			), $list);
		}

		$names = $this->getTemplateNames();
		$ret = array();
		$paths = array();

		foreach ($this->getPresenter()->formatTemplateFiles() as $file) {
			if (is_file($file)) {
				$paths[] = dirname($file);
				break;
			}
		}

		foreach ($this->getPresenter()->formatLayoutTemplateFiles() as $file) {
			if (is_file($file)) {
				$paths[] = dirname($file);
				break;
			}
		}

		foreach ($paths as $path) {
			if ($this->variant) {
				foreach ($names as $name) {
					$ret[] = $path . '/' . $name . '.' . $this->variant . '.latte';
				}
			}
			foreach ($names as $name) {
				$ret[] = $path . '/' . $name . '.latte';
			}
		}

		foreach ($paths as $path) {
			if ($this->variant) {
				foreach ($names as $name) {
					$ret[] = dirname($path) . '/' . $name . '.' . $this->variant . '.latte';
				}
			}
			foreach ($names as $name) {
				$ret[] = dirname($path) . '/' . $name . '.latte';
			}
		}

		$ret = array_merge($ret, $list);
		return $ret;
	}


	/**
	 * @return string
	 */
	protected function formatTemplateFile()
	{
		if (!isset($this->templateFiles[$this->variant])) {
			$this->templateFiles[$this->variant] = parent::formatTemplateFile();
		}

		return $this->templateFiles[$this->variant];
	}


	public function __call($name, $args)
	{
		if ($name === 'render') {
			if (isset($args[0]) && is_array($args[0]) && isset($args[0]['config'])) {
				$this->configureControl($args[0]['config']);
			}

			if (method_exists($this, 'renderDefault')) {
				call_user_func_array(array($this, 'renderDefault'), $args);
			}

			$this->template->setFile($this->formatTemplateFile());
			$this->template->render();

			if (method_exists($this, 'afterRender')) {
				call_user_func_array(array($this, 'afterRender'), array());
			}

			if (isset($args[0]) && is_array($args[0]) && isset($args[0]['config'])) {
				$this->unconfigureControl();
			}

			return;
		}

		return parent::__call($name, $args);
	}


	protected function configureControl($config)
	{
		if (isset($config['variant'])) {
			$this->variant = $config['variant'];
		}

		if (isset($config['template'])) {
			$this->configZal['template'] = $this->template->getFile();
			$file = $this->presenter->context->venne->moduleHelpers->expandPath($config['template'], 'Resources/layouts');
			$this->template->setFile($file);
		}
	}


	protected function unconfigureControl()
	{
		if (isset($this->configZal['template'])) {
			$this->template->setFile($this->configZal['template']);
		}

		$this->variant = NULL;
	}
}
