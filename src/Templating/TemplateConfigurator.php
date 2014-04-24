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

use Nette\Bridges\ApplicationLatte\Template;
use Nette\DI\Container;
use Latte\Engine;
use Nette\Localization\ITranslator;
use Nette\Object;

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
		$latte = $template->getLatte();
		$callback = array($this->helpers, 'loader');
		$template->getLatte()->addFilter(NULL, function($name) use ($callback, $latte) {
			if ($res = call_user_func($callback, $name)) {
				$latte->addFilter($name, $res);
			}
		});
	}


	public function prepareFilters(Template $template)
	{

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
