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

use Venne;
use CmsModule\Content\Control;
use DoctrineModule\Repositories\BaseRepository;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class NavigationControl extends Control
{

	/** @var string */
	protected $routePrefix;

	/** @var BaseRepository */
	protected $pageRepository;


	function __construct($pageRepository, $routePrefix)
	{
		$this->pageRepository = $pageRepository;
		$this->routePrefix = $routePrefix;
	}


	public function getItems()
	{
		return $this->pageRepository->createQueryBuilder('a')
			->andWhere('a.parent IS NULL')
			->andWhere('a.translationFor IS NULL')
			->getQuery()->getResult();
	}


	public function render($startDepth = NULL, $maxDepth = NULL, $followActive = NULL)
	{
		$cacheKey = array(
			$this->presenter->page->id, $this->routePrefix, $startDepth, $maxDepth, $followActive, $this->getPresenter()->lang
		);

		$this->template->startDepth = $startDepth ? : 0;
		$this->template->maxDepth = $maxDepth ? : 1;
		$this->template->followActive = $followActive ? : false;
		$this->template->cacheKey = implode('|', $cacheKey);

		$this->template->render();
	}
}
