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


	public function __construct(PageRepository $pageRepository)
	{
		$this->pageRepository = $pageRepository;
	}


	public function create()
	{
		$table = new \CmsModule\Components\Table\TableControl;

		$table->setRepository($this->pageRepository);
		$table->setDql(function (\Doctrine\ORM\QueryBuilder $builder) {
			$builder->andWhere('a.translationFor IS NULL AND a.virtualParent IS NULL');
		});

		// columns
		$table->addColumn('name', 'Name')
			->setWidth('50%')
			->setSortable(TRUE)
			->setFilter();
		$table->addColumn('url', 'URL')
			->setWidth('25%')
			->setCallback(function ($entity) {
				return $entity->mainRoute->url;
			});
		$table->addColumn('languages', 'Languages')
			->setWidth('25%')
			->setCallback(function ($entity) {
				$ret = implode(", ", $entity->languages->toArray());
				foreach ($entity->translations as $translation) {
					$ret .= ', ' . implode(", ", $translation->languages->toArray());
				}
				return $ret;
			});

		$_this = $this;
		$table->onAttached[] = function ($table) use ($_this) {
			$_this->onAttached($table);
		};

		return $table;
	}
}