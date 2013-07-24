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

use CmsModule\Panels\Stopwatch;
use Doctrine\ORM\EntityManager;
use Venne\Application\UI\Presenter;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
abstract class BasePresenter extends Presenter
{

	/** @persistent */
	public $lang;

	const MODE_NORMAL = NULL;

	const MODE_EDIT = 1;

	/** @persistent */
	public $mode;

	/** @var EntityManager */
	private $entityManager;


	public function __construct()
	{
		Stopwatch::start();
		parent::__construct();
	}


	/**
	 * @param EntityManager $entityManager
	 */
	public function injectEntityManager(EntityManager $entityManager)
	{
		$this->entityManager = $entityManager;
	}


	/**
	 * @return EntityManager
	 */
	public function getEntityManager()
	{
		return $this->entityManager;
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

		// mode
		if ($this->mode && !$this->getUser()->isLoggedIn()) {
			$this->mode = self::MODE_NORMAL;
		}
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
	 * @param  \Nette\Application\IResponse optional catched exception
	 * @return void
	 */
	public function shutdown($response)
	{
		parent::shutdown($response);

		Stopwatch::stop("template render");
		Stopwatch::start();
	}
}
