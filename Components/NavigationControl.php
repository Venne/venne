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

use Venne;
use Venne\Application\UI\Control;
use DoctrineModule\ORM\BaseRepository;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class NavigationControl extends Control
{


	/** @var BaseRepository */
	protected $pageRepository;


	function __construct($pageRepository)
	{
		$this->pageRepository = $pageRepository;
	}


	public function getItems()
	{
		return $this->pageRepository->createQueryBuilder('a')
			->andWhere('a.parent IS NULL')
			->andWhere('a.translationFor IS NULL')
			->getQuery()->getResult();
	}


	public function render($startDepth = NULL, $maxDepth = NULL, $followActive = NULL)
	{
		$this->template->startDepth = $startDepth ? : 0;
		$this->template->maxDepth = $maxDepth ? : 1;
		$this->template->followActive = $followActive ? : false;
		$this->template->cacheKey = $this->presenter->page->id;

		parent::render();
	}

}
