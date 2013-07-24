<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace CmsModule\Components;

use CmsModule\Content\Control;
use CmsModule\Content\Repositories\PageRepository;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class BreadcrumbControl extends Control
{

	/** @var string */
	protected $routePrefix;

	/** @var PageRepository */
	protected $pageRepository;


	public function __construct($routePrefix, PageRepository $pageRepository)
	{
		parent::__construct();

		$this->routePrefix = $routePrefix;
		$this->pageRepository = $pageRepository;
	}


	public function renderDefault()
	{
		$cacheKey = array(
			$this->presenter->route->id, $this->routePrefix, $this->getPresenter()->lang
		);

		$this->template->cacheKey = implode('|', $cacheKey);
	}
}
