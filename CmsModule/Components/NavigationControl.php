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
use CmsModule\Content\WebsiteManager;
use CmsModule\Pages\Redirect\PageEntity;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class NavigationControl extends Control
{

	/** @var WebsiteManager */
	protected $websiteManager;

	/** @var PageRepository */
	protected $pageRepository;


	/**
	 * @param WebsiteManager $websiteManager
	 * @param PageRepository $pageRepository
	 */
	public function __construct(WebsiteManager $websiteManager, PageRepository $pageRepository)
	{
		parent::__construct();

		$this->websiteManager = $websiteManager;
		$this->pageRepository = $pageRepository;
	}


	public function getRoot()
	{
		return $this->pageRepository->createQueryBuilder('a')
			->andWhere('a.parent IS NULL AND a.previous IS NULL')
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
		if ($entity instanceof PageEntity) {
			if ($entity->page) {
				return $this->presenter->link('Route', array('route' => $entity->page->mainRoute));
			} else {
				return $this->template->basePath . '/' . $entity->redirectUrl;
			}
		} else {
			return $this->presenter->link('Route', array('route' => $entity->mainRoute));
		}
	}


	public function renderDefault($startDepth = NULL, $maxDepth = NULL, $followActive = NULL)
	{
		$cacheKey = array(
			$this->presenter->page->id, $this->websiteManager->routePrefix, $startDepth, $maxDepth, $followActive, $this->getPresenter()->lang
		);

		$this->template->startDepth = $startDepth ? : 0;
		$this->template->maxDepth = $maxDepth ? : 1;
		$this->template->followActive = $followActive ? : FALSE;
		$this->template->cacheKey = array(
			implode('|', $cacheKey),
			$this->presenter->user->isLoggedIn() ? $this->presenter->user->getRoles() : NULL,
		);
	}
}
