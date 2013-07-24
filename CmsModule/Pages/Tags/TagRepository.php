<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace CmsModule\Pages\Tags;

use DoctrineModule\Repositories\BaseRepository;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class TagRepository extends BaseRepository
{

	/** @var TagsPageEntity */
	private $tagsPageEntity;


	/**
	 * @param array $arguments
	 * @return TagEntity
	 */
	public function createNew($arguments = array())
	{
		return parent::createNew(array($this->getTagsPageEntity()));
	}


	/**
	 * @return null|TagsPageEntity
	 */
	private function getTagsPageEntity()
	{
		if (!$this->tagsPageEntity) {
			$this->tagsPageEntity = $this->getEntityManager()->getRepository('CmsModule\Pages\Tags\PageEntity')->findOneBy(array());
		}

		return $this->tagsPageEntity;
	}
}
