<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace CmsModule\Content\Components;

use CmsModule\Administration\Components\AdminGrid\AdminGrid;
use CmsModule\Content\Repositories\PageRepository;
use Nette\Callback;
use Nette\Object;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class ContentTableFactory extends Object
{

	/** @var array */
	public $onAttached;

	/** @var PageRepository */
	protected $pageRepository;


	/**
	 * @param PageRepository $pageRepository
	 */
	public function __construct(PageRepository $pageRepository)
	{
		$this->pageRepository = $pageRepository;
	}


	/**
	 * @return AdminGrid
	 */
	public function create()
	{
		$_this = $this;
		$adminGrid = new AdminGrid($this->pageRepository);

		$table = $adminGrid->getTable();

		$table->addColumn('name', 'Name')
			->setCustomRender(function ($entity) {
				return $entity->mainRoute->name;
			})
			->setSortable()
			->setFilter()
				->setSuggestion(function($item) { return $item->mainRoute->name; });
		$table->getColumn('name')->getCellPrototype()->width = '50%';
		$table->addColumn('mainRoute', 'URL')
			->setSortable()
			->setFilter()->setSuggestion();
		$table->getColumn('mainRoute')->getCellPrototype()->width = '25%';
		$table->addColumn('language', 'Language')
			->getCellPrototype()->width = '25%';

		$adminGrid->onAttached[] = function (AdminGrid $table) use ($_this) {
			$_this->onAttached($table);
		};

		return $adminGrid;
	}
}