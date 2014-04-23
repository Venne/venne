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

use Nette\Templating\FileTemplate;
use Venne\Templating\ITemplateConfigurator;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
trait ControlTrait
{

	/** @var ITemplateConfigurator|NULL */
	private $templateConfigurator;


	/**
	 * @param ITemplateConfigurator $configurator
	 */
	public function injectVenneControl(ITemplateConfigurator $configurator = NULL)
	{
		$this->templateConfigurator = $configurator;
	}


	/**
	 * @return \Venne\Templating\ITemplateConfigurator
	 */
	public function getTemplateConfigurator()
	{
		return $this->templateConfigurator;
	}


	/**
	 * Descendant can override this method to customize template compile-time filters.
	 *
	 * @param  Nette\Templating\Template
	 * @return void
	 */
	public function templatePrepareFilters($template)
	{
		if ($this->templateConfigurator !== NULL) {
			$this->templateConfigurator->prepareFilters($template);
		} else {
			$template->registerFilter(new \Nette\Latte\Engine);
		}
	}


	/**
	 * @param string|NULL $class
	 * @return \Nette\Templating\ITemplate
	 */
	protected function createTemplate($class = NULL)
	{
		$template = parent::createTemplate($class);

		if ($this->templateConfigurator !== NULL) {
			$this->templateConfigurator->configure($template);
		}

		if ($template instanceof FileTemplate) {
			$template->setFile($this->formatTemplateFile());
		}

		return $template;
	}


	/**
	 * Formats component template files
	 *
	 * @param string
	 * @return array
	 */
	protected function formatTemplateFiles()
	{
		$refl = $this->getReflection();
		$list = array(
			dirname($refl->getFileName()) . '/' . $refl->getShortName() . '.latte',
		);
		return $list;
	}


	/**
	 * Format component template file
	 *
	 * @param string
	 * @return string
	 * @throws \Nette\InvalidStateException
	 */
	protected function formatTemplateFile()
	{
		$files = $this->formatTemplateFiles();
		foreach ($files as $file) {
			if (file_exists($file)) {
				return $file;
			}
		}

		throw new \Nette\InvalidStateException("No template files found");
	}
}

