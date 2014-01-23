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
use CmsModule\Administration\Presenters\ContentPresenter;
use CmsModule\Content\Entities\ExtendedPageEntity;
use DoctrineModule\Repositories\BaseRepository;
use Grido\DataSources\Doctrine;
use Nette\Application\BadRequestException;
use Venne\Application\UI\Control;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class RouteItemsControl extends Control
{

	/** @var BaseRepository */
	private $repository;

	/** @var ExtendedPageEntity */
	private $extendedPage;


	/**
	 * @param BaseRepository $repository
	 * @param ExtendedPageEntity $extendedPage
	 */
	public function __construct(BaseRepository $repository, ExtendedPageEntity $extendedPage)
	{
		parent::__construct();

		$this->repository = $repository;
		$this->extendedPage = $extendedPage;
	}


	/**
	 * @return \DoctrineModule\Repositories\BaseRepository
	 */
	public function getRepository()
	{
		return $this->repository;
	}


	/**
	 * @return AdminGrid
	 */
	public function getTable()
	{
		return $this['table'];
	}


	public function render()
	{
		$this->template->render();
	}


	public function handlePublish($id)
	{
		if (!$entity = $this->repository->find($id)) {
			throw new BadRequestException;
		}

		$entity->route->published = !$entity->route->published;
		$this->repository->save($entity);

		if (!$this->presenter->isAjax()) {
			$this->redirect('this');
		}

		$this['table']->invalidateControl('table');
		$this->presenter->payload->url = $this->link('this', array('id' => NULL));
	}


	public function handlePreview($id)
	{
		if (!$entity = $this->repository->find($id)) {
			throw new BadRequestException;
		}
		$route = $entity->getRoute();

		if (!$route->published) {
			$session = $this->getPresenter()->getSession(ContentPresenter::PREVIEW_SESSION);
			$session->setExpiration('+ 2 minutes');
			if (!isset($session->routes)) {
				$session->routes = array();
			}
			$session->routes[$route->id] = TRUE;
		}

		$this->getPresenter()->redirect(':Cms:Pages:Text:Route:', array(
			'route' => $route,
			'lang'=> $this->getPresenter()->contentLang ?: $this->presenter->websiteManager->defaultLanguage,
		));
	}


	protected function attached($presenter)
	{
		parent::attached($presenter);

		$this['table']->getTable()->setTranslator($this->presenter->context->translator->translator);
	}


	/**
	 * @return AdminGrid
	 */
	protected function createComponentTable()
	{
		$_this = $this;
		$admin = new AdminGrid($this->repository);

		// columns
		$table = $admin->getTable();
		$table->setModel(new Doctrine($this->repository->createQueryBuilder('a')
				->andWhere('a.extendedPage = :page')
				->setParameter('page', $this->extendedPage->id)
		));

		$table->addColumnText('name', 'Name')
			->setCustomRender(function ($entity) {
				return $entity->route->name;
			})
			->setSortable()
			->getCellPrototype()->width = '100%';
		$table->getColumn('name')
			->setFilterText()->setSuggestion();

		// actions
		$table->addAction('publish', 'Published')
			->setCustomRender(function ($entity, $element) {
				if ((bool)$entity->route->published) {
					$element->class[] = 'btn-primary';
				};
				return $element;
			})
			->setCustomHref(function ($entity) use ($_this) {
				return $_this->link('publish!', array($entity->id));
			})
			->getElementPrototype()->class[] = 'ajax';
		$table->addAction('preview', 'Preview')
			->setCustomHref(function ($entity) use ($_this) {
				return $_this->link('preview!', array($entity->id));
			});

		$table->addAction('edit', 'Edit')
			->getElementPrototype()->class[] = 'ajax';

		return $admin;
	}
}
