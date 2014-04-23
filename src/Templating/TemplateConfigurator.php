<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Templating;

use Nette\Callback;
use Nette\DI\Container;
use Nette\Latte\Engine;
use Nette\Localization\ITranslator;
use Nette\Object;
use Nette\Templating\Template;

/**
 * @author     Josef Kříž
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class TemplateConfigurator extends Object implements ITemplateConfigurator
{

	/** @var \SystemContainer|Container */
	private $container;

	/** @var Engine */
	private $latte;

	/** @var ITranslator */
	private $translator;

	/** @var Helpers */
	private $helpers;


	/**
	 * @param Container $container
	 * @param Engine $latte
	 * @param Helpers $helpers
	 * @param ITranslator $translator
	 */
	public function __construct(Container $container, Engine $latte, Helpers $helpers, ITranslator $translator = NULL)
	{
		$this->container = $container;
		$this->latte = $latte;
		$this->helpers = $helpers;
		$this->translator = $translator;
	}


	/**
	 * @param Template $template
	 */
	public function configure(Template $template)
	{
		// translator
		if ($this->translator) {
			$template->setTranslator($this->translator);
		}
		$template->registerHelperLoader(array($this->helpers, 'loader'));
	}


	public function prepareFilters(Template $template)
	{
		$template->registerFilter($this->latte);
	}


	/**
	 * Returns Latter parser for the last prepareFilters call.
	 *
	 * @return Engine
	 */
	public function getLatte()
	{
		return $this->latte;
	}
}
