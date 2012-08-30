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
use CmsModule\Content\Repositories\LanguageRepository;
use CmsModule\Content\Repositories\PageRepository;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class SitemapPresenter extends \Venne\Application\UI\Presenter
{

	/** @persistent */
	public $lang;

	/** @var string */
	protected $routePrefix;

	/** @var LanguageRepository */
	protected $languageRepository;

	/** @var PageRepository */
	protected $pageRepository;


	/**
	 * @param LanguageRepository $languageRepository
	 * @param PageRepository $pageRepository
	 * @param $routePrefix
	 */
	public function __construct(LanguageRepository $languageRepository, PageRepository $pageRepository, $routePrefix)
	{
		parent::__construct();

		$this->routePrefix = $routePrefix;
		$this->languageRepository = $languageRepository;
		$this->pageRepository = $pageRepository;

		$this->absoluteUrls = true;

		\Nette\Diagnostics\Debugger::$bar = false;
	}


	protected function beforeRender()
	{
		parent::beforeRender();

		$this->template->routePrefix = $this->routePrefix;
		$this->template->languageRepository = $this->languageRepository;
		$this->template->pageRepository = $this->pageRepository;
		$this->template->lang = $this->lang;
	}
}

