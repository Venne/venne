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

use CmsModule\Content\Forms\RouteFormFactory;
use CmsModule\Content\Repositories\RouteRepository;
use CmsModule\Content\SectionControl;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class RouteControl extends SectionControl
{

	/** @var RouteRepository */
	protected $routeRepository;

	/** @var RouteFormFactory */
	protected $routeFormFactory;


	/**
	 * @param RouteRepository $routeRepository
	 * @param \CmsModule\Content\Forms\RouteFormFactory $routeFormFactory
	 */
	public function __construct(RouteRepository $routeRepository, RouteFormFactory $routeFormFactory)
	{
		parent::__construct();

		$this->routeRepository = $routeRepository;
		$this->routeFormFactory = $routeFormFactory;
	}


	protected function createComponentTable()
	{
		$table = new \CmsModule\Components\Table\TableControl;
		$table->setTemplateConfigurator($this->templateConfigurator);
		$table->setRepository($this->routeRepository);

		$pageId = $this->getEntity()->id;
		$table->setDql(function ($sql) use ($pageId) {
			$sql = $sql->andWhere('a.page = :page')->setParameter('page', $pageId);
			return $sql;
		});

		// forms
		$form = $table->addForm($this->routeFormFactory, 'Route', NULL, \CmsModule\Components\Table\Form::TYPE_LARGE);

		$table->addColumn('title', 'Title')
			->setWidth('50%')
			->setSortable(TRUE)
			->setFilter();

		$table->addColumn('url', 'Url')
			->setWidth('50%')
			->setSortable(TRUE)
			->setFilter();

		$table->addActionEdit('edit', 'Edit', $form);

		return $table;
	}


	public function render()
	{
		$this->template->render();
	}
}
