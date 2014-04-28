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

use Nette\Application\UI\ITemplate;
use Nette\Application\UI\Presenter;
use Nette\Localization\ITranslator;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
trait ControlTrait
{

	/** @var ITranslator */
	private $translator;

	/** @var ITemplateLocator */
	private $templateLocator;


	/**
	 * @param ITranslator $translator
	 * @param ITemplateLocator $templateLocator
	 */
	public function injectControlTrait(ITranslator $translator, ITemplateLocator $templateLocator)
	{
		$this->translator = $translator;
		$this->templateLocator = $templateLocator;
	}


	/**
	 * @return ITemplate
	 */
	protected function createTemplate()
	{
		$template = parent::createTemplate();
		$template->setFile($this->formatTemplateFile());

		if ($this->translator) {
			$template->setTranslator($this->translator);
		}

		return $template;
	}


	public function formatTemplateFiles()
	{
		if ($this->templateLocator) {
			return $this->templateLocator->formatTemplateFiles($this);
		}

		if ($this instanceof Presenter) {
			return parent::formatTemplateFiles();
		}

		return array();
	}


	public function formatLayoutTemplateFiles()
	{
		if ($this->templateLocator) {
			return $this->templateLocator->formatLayoutTemplateFiles($this);
		}

		if ($this instanceof Presenter) {
			return parent::formatLayoutTemplateFiles();
		}
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

