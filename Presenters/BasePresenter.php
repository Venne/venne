<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace CmsModule\Presenters;

use Venne;
use CmsModule\Panels\Stopwatch;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
abstract class BasePresenter extends \Venne\Application\UI\Presenter
{

	/** @persistent */
	public $lang;


	public function __construct()
	{
		Stopwatch::start();
		parent::__construct();
	}


	/**
	 * @return void
	 */
	public function startup()
	{
		parent::startup();

		// Language
		$this->checkLanguage();

		// Setup translator
		if (($translator = $this->context->getByType('Nette\Localization\ITranslator', FALSE)) !== NULL) {
			$translator->setLang($this->lang);
		}

		// Stopwatch
		Stopwatch::stop("base startup");
		Stopwatch::start();
	}


	protected function checkLanguage()
	{

	}


	/**
	 * Common render method.
	 *
	 * @return void
	 */
	public function beforeRender()
	{
		// Stopwatch
		Stopwatch::stop("module startup and action");
		Stopwatch::start();

		parent::beforeRender();
	}


	/**
	 * @param  Nette\Application\IResponse  optional catched exception
	 * @return void
	 */
	public function shutdown($response)
	{
		parent::shutdown($response);

		Stopwatch::stop("template render");
		Stopwatch::start();
	}
}
