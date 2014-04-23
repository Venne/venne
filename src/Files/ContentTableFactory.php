<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Files;

use Venne\System\Components\AdminGrid\AdminGrid;
use Venne\System\Content\Repositories\PageRepository;
use Venne\BaseFactory;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class ContentTableFactory extends BaseFactory
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
	public function invoke()
	{
		$_this = $this;
		$adminGrid = new AdminGrid($this->pageRepository);

		$table = $adminGrid->getTable();
		$table->setTranslator($this->presenter->context->translator->translator);

		$table->addColumnText('mainRoutefdsf', 'Name')
//			->setCustomRender(function ($entity) {
//				return $entity->mainRoute->name;
//			})
			->setSortable()
			->setFilterText()->setSuggestion();
		$table->getColumn('name')->getCellPrototype()->width = '50%';
		$table->addColumnText('mainRoute', 'URL')
			->setSortable()
			->setFilterText()->setSuggestion();
		$table->getColumn('mainRoute')->getCellPrototype()->width = '25%';
		$table->addColumnText('language', 'Language')
			->getCellPrototype()->width = '25%';

		$adminGrid->onAttached[] = function (AdminGrid $table) use ($_this) {
			$_this->onAttached($table);
		};

		return $adminGrid;
	}
}