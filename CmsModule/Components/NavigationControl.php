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
class NavigationControl extends Control
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


	public function getRoot()
	{
		return $this->pageRepository->createQueryBuilder('a')
			->andWhere('a.parent IS NULL AND a.previous IS NULL AND a.virtualParent IS NULL')
			->andWhere('a.translationFor IS NULL')
			->getQuery()->getSingleResult();
	}


	public function isActive(\CmsModule\Content\Entities\PageEntity $page)
	{
		$currentUrl = $this->presenter->route->url;
		$url = $page->mainRoute->url;

		return (!$url && !$currentUrl) || ($url && strpos($currentUrl . '/', $url . '/') !== FALSE);
	}


	public function getLink(\CmsModule\Content\Entities\PageEntity $entity)
	{
		if ($entity instanceof \PagesModule\Entities\RedirectEntity) {
			if ($entity->page) {
				return $this->presenter->link('this', array('route' => $entity->page->mainRoute));
			} else {
				return $this->template->basePath . '/' . $entity->redirectUrl;
			}
		} else {
			return $this->presenter->link('this', array('route' => $entity->mainRoute));
		}
	}


	public function render($startDepth = NULL, $maxDepth = NULL, $followActive = NULL, $showMain = FALSE)
	{
		$cacheKey = array(
			$this->presenter->page->id, $this->routePrefix, $startDepth, $maxDepth, $followActive, $showMain, $this->getPresenter()->lang
		);

		$this->template->startDepth = $startDepth ? : 0;
		$this->template->maxDepth = $maxDepth ? : 1;
		$this->template->followActive = $followActive ? : FALSE;
		$this->template->showMain = $showMain;
		$this->template->cacheKey = implode('|', $cacheKey);

		$this->template->render();
	}
}
