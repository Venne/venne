<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace CmsModule\Pages\Sitemap;

use CmsModule\Content\Presenters\PagePresenter;
use CmsModule\Content\Repositories\PageRepository;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 *
 * @property-read PageEntity $extendedPage
 */
class RoutePresenter extends PagePresenter
{

	/** @var PageRepository */
	protected $pageRepository;


	/**
	 * @param PageRepository $pageRepository
	 */
	public function injectPageRepository(PageRepository $pageRepository)
	{
		$this->pageRepository = $pageRepository;
	}


	/**
	 * @return \CmsModule\Content\Entities\PageEntity
	 */
	public function getRootPage()
	{
		return $this->extendedPage->rootPage
			? $this->extendedPage->rootPage
			: $this->pageRepository->createQueryBuilder('a')
			->leftJoin('a.mainRoute', 'r')
			->where('r.url = :url')->setParameter('url', '')
			->getQuery()->getSingleResult();
	}

}
