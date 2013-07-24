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
use CmsModule\Pages\Tags\TagRepository;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class TagCloudControl extends Control
{

	/** @var TagRepository */
	private $tagRepository;


	/**
	 * @param TagRepository $tagRepository
	 */
	public function __construct(TagRepository $tagRepository)
	{
		parent::__construct();

		$this->tagRepository = $tagRepository;
	}


	/**
	 * @return array
	 */
	public function getTags()
	{
		$ret = array();
		foreach ($this->tagRepository->findAll() as $entity) {
			$ret[count($entity->getRoutes())][] = $entity;
		}

		return $ret;
	}
}
