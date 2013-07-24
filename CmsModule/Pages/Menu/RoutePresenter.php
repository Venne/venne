<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace CmsModule\Pages\Menu;

use CmsModule\Content\Presenters\ItemsPresenter;
use CmsModule\Content\Repositories\RouteRepository;
use DoctrineModule\Repositories\BaseRepository;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class RoutePresenter extends ItemsPresenter
{

	/** @var RouteRepository */
	private $routeRepository;


	/**
	 * @param RouteRepository $routeRepository
	 */
	public function injectRouteRepository(RouteRepository $routeRepository)
	{
		$this->routeRepository = $routeRepository;
	}


	/**
	 * @return RouteRepository
	 */
	protected function getRepository()
	{
		return $this->routeRepository;
	}


	protected function getItemsPerPage()
	{
		return $this->extendedPage->itemsPerPage;
	}


	/**
	 * @return \Doctrine\ORM\QueryBuilder
	 */
	protected function getQueryBuilder()
	{
		return parent::getQueryBuilder()
			->andWhere('a.parent = :route')->setParameter('route', $this->route);
	}
}
