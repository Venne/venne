<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace CmsModule\Pages\Rss;

use CmsModule\Content\Presenters\ItemsPresenter;
use CmsModule\Content\Repositories\RouteRepository;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class RssPresenter extends ItemsPresenter
{

	/** @var RouteRepository */
	protected $repository;


	/**
	 * @param RouteRepository $repository
	 */
	public function injectRepository(RouteRepository $repository)
	{
		$this->repository = $repository;
	}


	public function renderDefault()
	{
		$this->template->host = $this->getHttpRequest()->getUrl()->host;
		$this->template->scheme = $this->getHttpRequest()->getUrl()->scheme;
	}


	/**
	 * @return RssRepository
	 */
	protected function getRepository()
	{
		return $this->repository;
	}


	protected function getItemsPerPage()
	{
		return $this->extendedRoute->items;
	}


	protected function getQueryBuilder()
	{
		$qb = parent::getQueryBuilder();

		if ($this->extendedRoute->class) {
			$qb = $qb->andWhere('a.class = :class')->setParameter('class', $this->extendedRoute->class);
		}

		if (count($this->extendedRoute->targetPages)) {
			$ids = array();

			foreach ($this->extendedRoute->targetPages as $page) {
				$ids[] = $page->id;
			}

			$qb = $qb->andWhere('a.page IN (:page)')->setParameter('page', $ids);
		}

		return $qb;
	}

}
