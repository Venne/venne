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
use Doctrine\Common\Collections\Criteria;

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


	/**
	 * @param null $startDepth
	 * @param null $maxDepth
	 * @param \CmsModule\Content\Entities\PageEntity $root
	 */
	public function renderDefault($startDepth = NULL, $maxDepth = NULL, \CmsModule\Content\Entities\PageEntity $root = NULL)
	{
		$this->template->startDepth = $startDepth ? : 0;
		$this->template->maxDepth = $maxDepth ? : 2;
		$this->template->root = $root ? : NULL;
	}


	/**
	 * @return \CmsModule\Content\Entities\PageEntity
	 */
	public function getRoot()
	{
		return $this->pageRepository->createQueryBuilder('a')
			->andWhere('a.parent IS NULL AND a.previous IS NULL')
			->getQuery()->getSingleResult();
	}


	/**
	 * @param \CmsModule\Content\Entities\PageEntity $page
	 * @return int
	 */
	public function countChildren(\CmsModule\Content\Entities\PageEntity $page = NULL)
	{
		return $this->getChildrenQb($page)
			->select('COUNT(a.id)')->getQuery()->getSingleScalarResult();
	}


	/**
	 * @param \CmsModule\Content\Entities\PageEntity $page
	 * @return array
	 */
	public function getChildren(\CmsModule\Content\Entities\PageEntity $page = NULL)
	{
		return $this->getChildrenQb($page)
			->getQuery()->getResult();
	}


	/**
	 * @param \CmsModule\Content\Entities\PageEntity $page
	 * @return \Doctrine\ORM\QueryBuilder
	 */
	private function getChildrenQb(\CmsModule\Content\Entities\PageEntity $page = NULL)
	{
		return $this->pageRepository->createQueryBuilder('a')
			->leftJoin('a.mainRoute', 'r')
			->andWhere('a.parent = :parent')->setParameter('parent', $page ? $page->id : NULL)
			->andWhere('(r.language IS NULL OR r.language = :language)')->setParameter('language', $this->presenter->language->id)
			->andWhere('a.published = :true')
			->andWhere('r.published = :true')->setParameter('true', TRUE);
	}


	/**
	 * @param \CmsModule\Content\Entities\PageEntity $page
	 * @return bool
	 */
	public function isActive(\CmsModule\Content\Entities\PageEntity $page)
	{
		return $this->isUrlActive($page->mainRoute->url);
	}


	/**
	 * @param $url
	 * @return bool
	 */
	public function isUrlActive($url)
	{
		$currentUrl = $this->presenter->slug;
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

}
