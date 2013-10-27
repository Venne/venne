<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace CmsModule\Administration\Components;

use CmsModule\Administration\Components\AdminGrid\AdminGrid;
use CmsModule\Content\Forms\RouteFormFactory;
use CmsModule\Content\Repositories\RouteRepository;
use CmsModule\Content\SectionControl;
use Grido\DataSources\Doctrine;

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
		$admin = new AdminGrid($this->routeRepository);

		// columns
		$table = $admin->getTable();
		$table->setModel(new Doctrine($this->routeRepository->createQueryBuilder('a')
				->andWhere('a.page = :page')
				->setParameter('page', $this->entity->page->id)
		));
		$table->setTranslator($this->presenter->context->translator->translator);
		$table->addColumnText('title', 'Title')
			->setSortable()
			->getCellPrototype()->width = '100%';
		$table->getColumn('title')
			->setFilterText()->setSuggestion();

		$table->addColumnText('url', 'Url')
			->setSortable()
			->getCellPrototype()->width = '100%';
		$table->getColumn('url')
			->setFilterText()->setSuggestion();

		// actions
		$table->addAction('edit', 'Edit')
			->getElementPrototype()->class[] = 'ajax';

		$form = $admin->createForm($this->routeFormFactory, 'Route', NULL, \CmsModule\Components\Table\Form::TYPE_LARGE);

		$admin->connectFormWithAction($form, $table->getAction('edit'));


		return $admin;
	}


	public function render()
	{
		$this->template->render();
	}
}
