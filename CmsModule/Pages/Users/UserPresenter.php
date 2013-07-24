<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace CmsModule\Pages\Users;

use CmsModule\Content\Presenters\ItemsPresenter;
use CmsModule\Content\Repositories\RouteRepository;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class UserPresenter extends ItemsPresenter
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


	/**
	 * @return RouteRepository
	 */
	protected function getRepository()
	{
		return $this->repository;
	}


	protected function getItemsPerPage()
	{
		return $this->extendedPage->itemsPerPage;
	}


	/**
	 * @return \Doctrine\ORM\QueryBuilder
	 */
	public function getQueryBuilder()
	{
		return parent::getQueryBuilder()
			->andWhere('a.author = :id')
			->setParameter('id', $this->extendedRoute->id);
	}
}
