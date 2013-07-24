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

/**
 * @author Josef Kříž <pepakriz@gmail.com>
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
	 * Formats component template files
	 *
	 * @param string
	 * @return array
	 */
	protected function formatTemplateFiles()
	{
		$list = parent::formatTemplateFiles();
		$refl = $this->getReflection();

		foreach ($this->getPresenter()->formatTemplateFiles() as $file) {
			if (is_file($file)) {
				$path1 = dirname($file);
				break;
			}
		}

		foreach ($this->getPresenter()->formatLayoutTemplateFiles() as $file) {
			if (is_file($file)) {
				$path2 = dirname($file);
				break;
			}
		}

		$ret = array_merge(array(
			dirname($path1) . '/' . $refl->getShortName() . '.latte',
			dirname($path2) . '/' . $refl->getShortName() . '.latte',
			$path1 . '/' . $refl->getShortName() . '.latte',
			$path2 . '/' . $refl->getShortName() . '.latte',
		), $list);

		if ($this->variant) {
			$ret = array_merge(array(
				dirname($path1) . '/' . $refl->getShortName() . '.' . $this->variant . '.latte',
				dirname($path2) . '/' . $refl->getShortName() . '.' . $this->variant . '.latte',
				$path1 . '/' . $refl->getShortName() . '.' . $this->variant . '.latte',
				$path2 . '/' . $refl->getShortName() . '.' . $this->variant . '.latte',
			), $ret);
		}

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
