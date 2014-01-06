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

use CmsModule\Components\PaginationControl;
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

		$offset = $this['pagination']->getPaginator()->getOffset();
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
				->andWhere('r.released <= :released')->setParameter('released', new \Nette\DateTime())
				->andWhere('(r.expired >= :expired OR r.expired IS NULL)')->setParameter('expired', new \Nette\DateTime());

			if (count($this->websiteManager->languages) > 1) {
				$qb->andWhere('(r.language IS NULL OR r.language = :language)')->setParameter('language', $this->getLanguage()->id);
			}
		} else {
			$qb
				->andWhere('a.published = :true')->setParameter('true', TRUE)
				->andWhere('a.released <= :released')->setParameter('released', new \Nette\DateTime())
				->andWhere('(a.expired >= :expired OR a.expired IS NULL)')->setParameter('expired', new \Nette\DateTime());

			if (count($this->websiteManager->languages) > 1) {
				$qb->andWhere('(a.language IS NULL OR a.language = :language)')->setParameter('language', $this->getLanguage()->id);
			}
		}

		return $qb;
	}


	protected function getCountItems()
	{
		return $this->getQueryBuilder()->select('COUNT(a.id)')->getQuery()->getSingleScalarResult();
	}


	/**
	 * @return PaginationControl
	 */
	protected function createComponentPagination()
	{
		$vp = new PaginationControl;
		$vp->onAction[] = function(PaginationControl $vp) {
			$pg = $vp->getPaginator();
			$pg->setItemCount($this->getCountItems());
			if (($itemsPerPage = $this->getItemsPerPage()) !== NULL) {
				$pg->setItemsPerPage($itemsPerPage);
			} else {
				$pg->setItemsPerPage(999999999999999);
			}
		};

		return $vp;
	}
}
