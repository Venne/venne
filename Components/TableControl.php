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

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class TableControl extends Control
{

	/** @var string */
	protected $templateFile;

	/** @var \DoctrineModule\ORM\BaseRepository */
	protected $repository;

	/** @var string */
	protected $primaryColumn;

	/** @var array */
	protected $columns = array();

	/** @var array */
	protected $actions = array();

	/** @var array */
	protected $globalActions = array();

	/** @var int */
	protected $paginator;

	/** @var bool */
	protected $sorter;

	/** @var \Nette\Callback */
	protected $dqlCallback;

	/** @persistent */
	public $sort;

	/** @persistent */
	public $order = 'ASC';


	public function __construct($primaryColumn = 'id')
	{
		parent::__construct();

		$this->primaryColumn = $primaryColumn;

		$this->invalidateControl("table");
	}


	public function setTemplateFile($file)
	{
		$this->templateFile = $file;
	}


	public function enableSorter()
	{
		$this->sorter = true;
	}


	public function getRepository()
	{
		return $this->repository;
	}


	public function setRepository(\DoctrineModule\ORM\BaseRepository $repository)
	{
		$this->repository = $repository;
	}


	public function setPaginator($itemOnPage = 10)
	{
		$this->paginator = $itemOnPage;
	}


	public function addColumn($name, $title, $width = NULL, $callback = NULL)
	{
		$this->columns[$name] = array(
			'title' => $title,
			'width' => $width,
			'callback' => $callback,
		);
	}


	public function addAction($name, $title, $callback)
	{
		$this->actions[$name] = array(
			'title' => $title,
			'callback' => $callback,
		);
	}


	public function addGlobalAction($name, $title, $callback)
	{
		$this->globalActions[$name] = array(
			'title' => $title,
			'callback' => $callback,
		);
	}


	public function handleDoAction($name, $id)
	{
		$callback = $this->actions[$name]['callback'];
		$callback($this->repository->find($id));
	}


	public function render()
	{
		$this->template->columns = $this->columns;
		$this->template->actions = $this->actions;
		$this->template->globalActions = $this->globalActions;
		$this->template->primaryColumn = $this->primaryColumn;
		$this->template->paginator = $this->paginator;
		$this->template->sorter = $this->sorter;

		if ($this->templateFile) {
			$this->template->setFile($this->templateFile);
		}

		parent::render();
	}


	public function getItems()
	{
		$dql = $this->repository->createQueryBuilder('a');

		if ($this->dqlCallback) {
			$fn = $this->dqlCallback;
			$fn($dql);
		}

		if ($this->sorter && $this->sort) {
			$dql = $dql->orderBy(array($this->sorter => $this->sort));
		}

		if ($this->paginator) {
			$dql = $dql
				->setMaxResults($this->paginator)
				->setFirstResult(($this["vp"]->page - 1) * $this->paginator);
		}

		return $dql->getQuery()->getResult();
	}


	protected function createComponentVp()
	{
		$vp = new \CmsModule\Components\VisualPaginator;
		$pg = $vp->getPaginator();
		$pg->setItemsPerPage($this->paginator);
		$pg->setItemCount($this->repository->createQueryBuilder("a")->select("COUNT(a.{$this->primaryColumn})")->getQuery()->getSingleScalarResult());
		return $vp;
	}


	protected function createComponentActionForm()
	{
		$form = new Venne\Application\UI\Form;

		// items
		$items = $form->addContainer('items');
		foreach ($this->getItems() as $entity) {
			$items->addCheckbox('item_' . $entity->{$this->primaryColumn});
		}

		// actions
		$items = array();
		foreach ($this->globalActions as $key => $item) {
			$items[$key] = $item['title'];
		}

		$form->addSelect('action', 'Action', $items);
		$form->addSubmit('_submit', 'Submit');
		$form->onSuccess[] = callback($this, 'formSuccess');
		return $form;
	}


	public function formSuccess($form)
	{
		$action = $form['action']->getValue();
		$callback = $this->globalActions[$action]['callback'];

		$values = $form['items']->getValues();
		foreach ($values as $key => $value) {
			if ($value) {
				$entity = $this->repository->find(substr($key, 5));
				$callback($entity);
			}
		}
	}


	/**
	 * @param \Nette\Callback $dql
	 */
	public function setDql($dql)
	{
		$this->dqlCallback = $dql;
	}


	/**
	 * @return \Nette\Callback
	 */
	public function getDql()
	{
		return $this->dqlCallback;
	}
}
