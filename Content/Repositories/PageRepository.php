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

use Venne;
use DoctrineModule\Repositories\BaseRepository;
use CmsModule\Content\Entities\PageEntity;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class PageRepository extends BaseRepository
{


	public function createNewByEntityName($entityName)
	{
		$class = '\\' . $entityName;
		$entity = new $class;

		if (!$entity instanceof PageEntity) {
			throw new \Nette\InvalidArgumentException('Entity must be instance of CmsModule\Content\Entities\PageEntity.');
		}

		// set default language
		$entity->languages->add($this->getEntityManager()->getRepository('CmsModule\Content\Entities\LanguageEntity')->findOneBy(array(), array('id' => 'ASC'), 1));

		// set to root as last entity
		$last = $this->findBy(array('parent' => NULL), array('order' => 'DESC'), 1);
		if (isset($last[0])) {
			$entity->setParent(NULL, true, $last[0]);
		}

		return $entity;
	}


	public function save($entity, $withoutFlush = self::FLUSH)
	{
		if(!$this->isUnique($entity)) {
			throw new \Nette\InvalidArgumentException('Entity is not unique!');
		}

		return parent::save($entity, $withoutFlush);
	}


	/**
	 * Check if page URL is unique.
	 *
	 * @param \CmsModule\Entities\PageEntity $page
	 * @return bool
	 */
	public function isUnique(PageEntity $page)
	{
//		$pages = $this->findBy(array("url" => $page->url));
//
//		if (!$pages || (count($pages) == 1 && $pages[0] === $page )) {
//			return true;
//		}
//
//		foreach ($pages as $item) {
//			if($item === $page){
//				continue;
//			}
//
//			foreach ($item->languages as $lang) {
//				if ($page->isInLanguageAlias($lang->alias)) {
//					return false;
//				}
//			}
//		}
		return true;
	}
}
