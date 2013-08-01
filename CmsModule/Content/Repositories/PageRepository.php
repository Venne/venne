<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace CmsModule\Content\Repositories;

use CmsModule\Content\Entities\ExtendedPageEntity;
use CmsModule\Content\Entities\PageEntity;
use DoctrineModule\Repositories\BaseRepository;
use Nette\InvalidArgumentException;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class PageRepository extends BaseRepository
{

	public function createNewByEntityName($entityName)
	{
		$entity = $this->createNewRawByEntityName($entityName);

		// set to root as last entity
		$root = $this->findOneBy(array('parent' => NULL, 'previous' => NULL));
		if ($root) {
			$last = $this->findBy(array('parent' => $root->id), array('position' => 'DESC'), 1);
			$entity->page->setParent($root, true, isset($last[0]) ? $last[0] : NULL);
		}

		return $entity;
	}


	protected function createNewRawByEntityName($entityName)
	{
		$class = '\\' . $entityName;
		$entity = new $class;

		if (!$entity instanceof ExtendedPageEntity) {
			throw new \Nette\InvalidArgumentException("Entity must be instance of CmsModule\Content\Entities\ExtendedPageEntity. '" . get_class($entity) . "' given.");
		}

		return $entity;
	}


	public function save($entity, $withoutFlush = self::FLUSH)
	{
		$parent = $entity instanceof ExtendedPageEntity ? $entity->getPage() : $entity;
		$main = $parent;
		while (($parent = $parent->parent) !== NULL) {
			if ($main->id === $parent->id) {
				throw new \Nette\InvalidArgumentException('Cyclic association detected!');
			}
		}

		if (!$this->isUnique($entity)) {
			throw new \Nette\InvalidArgumentException('Entity is not unique!');
		}

		return parent::save($entity, $withoutFlush);
	}


	public function delete($entity, $withoutFlush = self::FLUSH)
	{
		if ($entity instanceof PageEntity) {
			$entity->removeFromPosition();
		} else if ($entity instanceof ExtendedPageEntity) {
			$entity->page->removeFromPosition();
		} else {
			throw new InvalidArgumentException("Entity must be instance of 'CmsModule\Content\Entities\PageEntity'. '" . get_class($entity) . "' given.");
		}

		return parent::delete($entity, $withoutFlush);
	}


	/**
	 * Check if page URL is unique.
	 *
	 * @param \CmsModule\Content\Entities\PageEntity $page
	 * @return bool
	 */
	public function isUnique($page)
	{
		$page = $page instanceof ExtendedPageEntity ? $page->getPage() : $page;

		$routeRepository = $this->getRouteRepository();

		foreach ($page->getRoutes() as $pageRoute) {
			foreach ($routeRepository->findBy(array('url' => $pageRoute->getUrl())) as $route) {
				if ($pageRoute->id !== $route->id) {
					if (!$route->page->language || $route->page->language->id === $pageRoute->page->language->id) {
						return FALSE;
					}
				}
			}
		}

		return TRUE;
	}


	/**
	 * @return BaseRepository
	 */
	protected function getRouteRepository()
	{
		return $this->getEntityManager()->getRepository('CmsModule\Content\Entities\RouteEntity');
	}
}
