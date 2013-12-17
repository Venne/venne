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

use DoctrineModule\Repositories\BaseRepository;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class RssRepository extends BaseRepository
{

	/** @var RssPageEntity */
	private $rssPageEntity;


	/**
	 * @param array $arguments
	 * @return TagEntity
	 */
	public function createNew($arguments = array())
	{
		return parent::createNew(array($this->getRssPageEntity()));
	}


	/**
	 * @return null|RssPageEntity
	 */
	private function getRssPageEntity()
	{
		if (!$this->rssPageEntity) {
			$this->rssPageEntity = $this->getEntityManager()->getRepository('CmsModule\Pages\Rss\PageEntity')->findOneBy(array());
		}

		return $this->rssPageEntity;
	}
}
