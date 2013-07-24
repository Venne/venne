<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace CmsModule\Pages\Search;

use CmsModule\Administration\Presenters\ContentPresenter;
use CmsModule\Content\Presenters\ItemsPresenter;
use CmsModule\Content\Repositories\RouteRepository;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class RoutePresenter extends ItemsPresenter
{

	/** @persistent */
	public $search;

	/** @var RouteRepository */
	protected $repository;

	/** @var FrontFormFactory */
	protected $frontFormFactory;


	/**
	 * @param RouteRepository $repository
	 */
	public function injectRepository(RouteRepository $repository)
	{
		$this->repository = $repository;
	}


	/**
	 * @param FrontFormFactory $frontFormFactory
	 */
	public function injectFrontFormFactory(FrontFormFactory $frontFormFactory)
	{
		$this->frontFormFactory = $frontFormFactory;
	}


	public function startup()
	{
		parent::startup();

		if ($this->search) {
			$q = clone $this->getQueryBuilder();
			$q->select('COUNT(a)');

			if ($q->getQuery()->getSingleScalarResult() == 1) {
				$route = $this->getQueryBuilder()->getQuery()->getSingleResult();
				$this->redirect('Route', array('route' => $route, 'search' => NULL));
			}
		}
	}


	/**
	 * @return UserRepository
	 */
	protected function getRepository()
	{
		return $this->repository;
	}


	protected function getItemsPerPage()
	{
		return $this->extendedPage->itemsPerPage;
	}


	/**
	 * @return \Doctrine\ORM\QueryBuilder
	 */
	public function getQueryBuilder()
	{
		if ($this->context->parameters['website']['defaultLanguage'] !== $this->lang) {
			return parent::getQueryBuilder()
				->leftJoin('a.translations', 'b')
				->andwhere('b.language = :langId')
				->andWhere('(b.name LIKE :search OR b.title LIKE :search OR b.notation LIKE :search OR b.text LIKE :search)')
				->setParameter('langId', $this->getLanguage()->id)
				->setParameter('search', "%{$this->search}%");
		} else {
			return parent::getQueryBuilder()
				->andWhere('a.name LIKE :search OR a.title LIKE :search OR a.notation LIKE :search OR a.text LIKE :search')
				->setParameter('search', "%{$this->search}%");
		}
	}


	protected function createComponentForm()
	{
		$form = $this->frontFormFactory->invoke();
		$form['search']->setDefaultValue($this->search);
		return $form;
	}
}
