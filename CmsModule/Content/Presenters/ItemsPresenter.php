<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace CmsModule\Content\Presenters;

use CmsModule\Components\VisualPaginator;
use CmsModule\Content\Repositories\RouteRepository;
use DoctrineModule\Repositories\BaseRepository;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
abstract class ItemsPresenter extends PagePresenter
{

	/**
	 * @return BaseRepository
	 */
	abstract protected function getRepository();


	/**
	 * @return int
	 */
	protected function getItemsPerPage()
	{
		return NULL;
	}


	/**
	 * @return \Doctrine\ORM\QueryBuilder
	 */
	public function getItemsBuilder()
	{
		$qb = $this->getQueryBuilder();

		if (($limit = $this->getItemsPerPage()) !== NULL) {
			$qb = $qb->setMaxResults($limit);
		}

		$offset = $this['vp']->getPaginator()->getOffset();
		if ($offset) {
			$qb = $qb->setFirstResult($offset);
		}

		return $qb;
	}


	/**
	 * @return \Doctrine\ORM\QueryBuilder
	 */
	protected function getQueryBuilder()
	{
		$qb = $this->getRepository()->createQueryBuilder('a');

		if (!$this->getRepository() instanceof RouteRepository) {
			$qb
				->join('a.route', 'r')
				->andWhere('r.published = :true')->setParameter('true', TRUE)
				->andWhere('r.page = :page')->setParameter('page', $this->page->id)
				->andWhere('r.released <= :released OR r.released IS NULL')->setParameter('released', new \Nette\DateTime())
				->andWhere('(r.expired >= :expired OR r.expired IS NULL)')->setParameter('expired', new \Nette\DateTime());
		} else {
			$qb
				->andWhere('a.published = :true')->setParameter('true', TRUE)
				->andWhere('a.released <= :released OR a.released IS NULL')->setParameter('released', new \Nette\DateTime())
				->andWhere('(a.expired >= :expired OR a.expired IS NULL)')->setParameter('expired', new \Nette\DateTime());
		}

		return $qb;
	}


	protected function getCountItems()
	{
		return $this->getQueryBuilder()->select('COUNT(a.id)')->getQuery()->getSingleScalarResult();
	}


	/**
	 * @return VisualPaginator
	 */
	protected function createComponentVp()
	{
		$vp = new VisualPaginator;
		$pg = $vp->getPaginator();
		$pg->setItemCount($this->getCountItems());
		if (($itemsPerPage = $this->getItemsPerPage()) !== NULL) {
			$pg->setItemsPerPage($itemsPerPage);
		} else {
			$pg->setItemsPerPage(999999999999999);
		}
		return $vp;
	}
}
