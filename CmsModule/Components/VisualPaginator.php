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
use Nette\Utils\Paginator;
use Venne\Application\UI\Control;
use CmsModule\Content\Presenters\PagePresenter;

/**
 * Visual paginator control.
 *
 * @author     David Grudl
 * @copyright  Copyright (c) 2009 David Grudl
 */
class VisualPaginator extends Control
{

	/** @var Paginator */
	private $paginator;

	/** @persistent */
	public $page = 1;


	/**
	 * @return Nette\Paginator
	 */
	public function getPaginator()
	{
		if (!$this->paginator) {
			$this->paginator = new Paginator;
		}
		return $this->paginator;
	}


	/**
	 * Renders paginator.
	 *
	 * @return void
	 */
	public function render()
	{
		$paginator = $this->getPaginator();
		$page = $paginator->page;
		if ($paginator->pageCount < 2) {
			$steps = array($page);
		} else {
			$arr = range(max($paginator->firstPage, $page - 3), min($paginator->lastPage, $page + 3));
			$count = 4;
			$quotient = ($paginator->pageCount - 1) / $count;
			for ($i = 0; $i <= $count; $i++) {
				$arr[] = round($quotient * $i) + $paginator->firstPage;
			}
			sort($arr);
			$steps = array_values(array_unique($arr));
		}

		$this->template->steps = $steps;
		$this->template->paginator = $paginator;

		$this->template->render();
	}


	/**
	 * Loads state informations.
	 *
	 * @param  array
	 * @return void
	 */
	public function loadState(array $params)
	{
		parent::loadState($params);
		$this->getPaginator()->page = $this->page;
	}


	/**
	 * Formats component template files
	 *
	 * @param string
	 * @return array
	 */
	protected function formatTemplateFiles()
	{
		if (!$this->presenter instanceof PagePresenter) {
			return parent::formatTemplateFiles();
		}

		$list = parent::formatTemplateFiles();
		$refl = $this->getReflection();
		$path = dirname($this->getPresenter()->getLayoutPath());

		return array_merge(array(
			dirname($path) . '/' . $refl->getShortName() . '.latte',
			$path . '/' . $refl->getShortName() . '.latte',
		), $list);
	}
}