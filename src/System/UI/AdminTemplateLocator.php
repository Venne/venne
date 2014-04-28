<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\System\UI;

use Nette\Application\UI\Presenter;
use Nette\ComponentModel\Component;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class AdminTemplateLocator implements ITemplateLocator
{

	/** @var array */
	private $templateDirs = array();


	public function __construct(array $templateDirs = array())
	{
		$this->templateDirs = $templateDirs;
	}


	public function formatLayoutTemplateFiles(Component $control)
	{
		$list = array();
		$name = $control->getName();
		$layout = $control->layout ? $control->layout : 'layout';

		$presenter = substr($name, strrpos(':' . $name, ':'));
		$absolutePresenter = str_replace(':', '/', $name);

		foreach ($this->templateDirs as $dir) {
			$name = $control->getName();
			$list[] = "$dir/$absolutePresenter/@$layout.latte";

			$dir = dirname("$dir/$absolutePresenter");
			do {
				$list[] = "$dir/@$layout.latte";
				$dir = dirname($dir);
			} while ($dir && ($name = substr($name, 0, strrpos($name, ':'))));
		}

		$dir = dirname($control->getReflection()->getFileName());
		foreach (array($dir . '/templates') as $dir) {
			$list[] = "$dir/$presenter/@$layout.latte";
			$list[] = "$dir/@$layout.latte";
		}

		return $list;
	}


	public function formatTemplateFiles(Component $control)
	{
		$list = array();
		$name = $control->getName();

		if (!$control instanceof Presenter) {
			$files = $this->formatTemplateFiles($control->presenter);
			foreach ($files as $file) {
				if (is_file($file)) {
					break;
				}
			}

			$layouts = $this->formatLayoutTemplateFiles($control->presenter);
			foreach ($layouts as $layout) {
				if (is_file($layout)) {
					break;
				}
			}

			foreach (array($file, $layout) as $dir) {
				do {
					$dir = dirname($dir);
					$list[] = "$dir/components/$name.latte";
				} while ($dir && substr($dir, strrpos($dir, '/') + 1) !== 'templates');
			}

			$list[] = dirname($control->getReflection()->getFileName()) . '/' . $control->getReflection()->getShortName() . '.latte';

			return $list;
		}

		$presenter = substr($name, strrpos(':' . $name, ':'));
		$absolutePresenter = str_replace(':', '/', $name);

		foreach ($this->templateDirs as $dir) {
			$list[] = "$dir/$absolutePresenter/$control->view.latte";
			$list[] = "$dir/$absolutePresenter.$control->view.latte";
		}

		$dir = dirname($control->getReflection()->getFileName());
		foreach (array($dir . '/templates') as $dir) {
			$list[] = "$dir/$presenter/$control->view.latte";
			$list[] = "$dir/$presenter.$control->view.latte";
		}

		return $list;
	}

}

