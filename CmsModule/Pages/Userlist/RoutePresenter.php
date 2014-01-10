<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace CmsModule\Pages\Userlist;

use CmsModule\Content\Presenters\ItemsPresenter;
use CmsModule\Security\Repositories\UserRepository;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class RoutePresenter extends ItemsPresenter
{

	/** @var UserRepository */
	protected $repository;


	/**
	 * @param UserRepository $repository
	 */
	public function injectRepository(UserRepository $repository)
	{
		$this->repository = $repository;
	}


	/**
	 * @return UserRepository
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
	public function getItemsBuilder()
	{
		$qb = parent::getItemsBuilder();

		if (count($this->extendedPage->roles)) {
			$ids = array();
			foreach ($this->extendedPage->roles as $role) {
				$ids[] = $role->id;
			}
			$qb
				->leftJoin('a.roleEntities', 'e')
				->andWhere('e.id IN (:ids)')
				->setParameter('ids', $ids);
		}

		return $qb;
	}


	/**
	 * @return \Doctrine\ORM\QueryBuilder
	 */
	protected function getQueryBuilder()
	{
		$qb = $this->getRepository()->createQueryBuilder('a')
			->join('a.route', 'r')
			->andWhere('r.published = :true')->setParameter('true', TRUE)
			->andWhere('r.released <= :released')->setParameter('released', new \DateTime)
			->andWhere('(r.expired >= :expired OR r.expired IS NULL)')->setParameter('expired', new \DateTime);

		if (count($this->websiteManager->languages) > 1) {
			$qb->andWhere('(r.language IS NULL OR r.language = :language)')->setParameter('language', $this->getLanguage()->id);
		}

		return $qb;
	}

}
