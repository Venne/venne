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

use CmsModule\Content\Presenters\ItemsPresenter;
use CmsModule\Pages\Tags\TagRepository;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class TagsPresenter extends ItemsPresenter
{

	/** @var TagRepository */
	protected $repository;


	/**
	 * @param TagRepository $repository
	 */
	public function injectRepository(TagRepository $repository)
	{
		$this->repository = $repository;
	}


	/**
	 * @return TagRepository
	 */
	protected function getRepository()
	{
		return $this->repository;
	}


	protected function getItemsPerPage()
	{
		return $this->extendedPage->itemsPerPage;
	}
}
