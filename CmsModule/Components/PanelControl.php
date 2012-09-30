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
use CmsModule\Services\ScannerService;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class PanelControl extends Control
{

	/** @var \Nette\Http\SessionSection */
	protected $session;

	/** @var ScannerService */
	protected $scannerService;


	/**
	 * @param \CmsModule\Services\ScannerService $scannerService
	 * @param \Nette\Http\SessionSection $session
	 */
	public function __construct(ScannerService $scannerService, \Nette\Http\SessionSection $session)
	{
		parent::__construct();

		$this->scannerService = $scannerService;
		$this->session = $session;
	}


	public function render()
	{
		$this->template->render();
	}


	public function getTab()
	{
		return $this->session->tab;
	}


	public function setTab($tab)
	{
		$this->session->tab = $tab;
	}


	public function handleTab($tab)
	{
		$this->invalidateControl('content');
		$this->tab = $tab;
		$this->getPresenter()->payload->url = $this->link('this');
	}


	protected function createComponentBrowser()
	{
		$nullLinkParams = \Venne\Application\UI\Helpers::nullLinkParams($this);

		if ($this->tab == 0) {
			$browser = new \CmsModule\Components\BrowserControl(callback($this, "getPages"), callback($this, "setPageParent"));
			$browser->setOnActivateLink($this->getPresenter()->link(':Cms:Admin:Content:edit', array('key' => 'this') + $nullLinkParams));
		} else if ($this->tab == 2) {
			$browser = new \CmsModule\Components\BrowserControl(callback($this, "getFiles"), callback($this, "setFileParent"));
			$browser->setOnActivateLink($this->getPresenter()->link(':Cms:Admin:Files:', array('key' => 'this') + $nullLinkParams));
		} else if ($this->tab == 3) {
			$browser = new \CmsModule\Components\BrowserControl(callback($this, "getLayouts"), callback($this, "setLayoutParent"));
			$browser->setOnActivateLink($this->getPresenter()->link(':Cms:Admin:Layouts:edit', array('key' => 'this') + $nullLinkParams));
		}
		$browser->setTemplateConfigurator($this->templateConfigurator);
		return $browser;
	}


	public function getLayouts($parent = NULL)
	{
		$data = array();
		$layouts = $this->scannerService->getLayoutFiles();

		if (!$parent) {
			foreach (array_keys($layouts) as $name) {
				$item = array('title' => $name, 'key' => $name, 'isFolder' => true, 'isLazy' => true);
				if ($name == 'app') {
					$item['expand'] = true;
					$item['children'] = $this->getLayouts($name);
				}
				$data[] = $item;
			}
		} else {
			foreach ($layouts[$parent] as $key => $name) {
				$data[] = array('title' => $name, 'key' => $key);
			}
		}

		return $data;
	}


	public function getPages($parent = NULL)
	{
		$repository = $this->getPresenter()->getContext()->cms->pageRepository;
		$data = array();

		$dql = $repository->createQueryBuilder('a');
		if ($parent) {
			$dql = $dql->andWhere('a.parent = ?1')->setParameter(1, $parent);
		} else {
			$dql = $dql->andWhere('a.parent IS NULL');
		}
		$dql
			->andWhere('a.translationFor IS NULL')
			->orderBy('a.order');

		foreach ($dql->getQuery()->getResult() as $page) {
			$item = array("title" => $page->name, 'key' => $page->id);

			if (count($page->childrens) > 0) {
				$item['isLazy'] = true;
			}

			if (!$page->parent) {
				$item['expand'] = true;
				$item['children'] = $this->getPages($page);
			}

			$data[] = $item;
		}
		return $data;
	}


	public function expandPage($page, $mode)
	{
		dump($page);
		dump($mode);
		die();
	}


	public function getFiles($parent = NULL)
	{
		$parent = $parent ? substr($parent, 2) : NULL;

		$dirRepository = $this->getPresenter()->getContext()->cms->dirRepository;
		$fileRepository = $this->getPresenter()->getContext()->cms->fileRepository;
		$data = array();

		$dql = $dirRepository->createQueryBuilder('a');
		if ($parent) {
			$dql = $dql->andWhere('a.parent = ?1')->setParameter(1, $parent);
		} else {
			$dql = $dql->andWhere('a.parent IS NULL');
		}
		$dql = $dql->andWhere('a.invisible = :invisible')->setParameter('invisible', false);

		foreach ($dql->getQuery()->getResult() as $page) {
			$item = array("title" => $page->name, 'key' => 'd:' . $page->id);

			$item["isFolder"] = true;

			if (count($page->childrens) > 0 || count($page->files) > 0) {
				$item['isLazy'] = true;
			}

			$data[] = $item;
		}

		$dql = $fileRepository->createQueryBuilder('a');
		if ($parent) {
			$dql = $dql->andWhere('a.parent = ?1')->setParameter(1, $parent);
		} else {
			$dql = $dql->andWhere('a.parent IS NULL');
		}

		foreach ($dql->getQuery()->getResult() as $page) {
			$item = array("title" => $page->name, 'key' => 'f:' . $page->id);
			$data[] = $item;
		}

		return $data;
	}


	public function setPageParent($from, $to, $dropmode)
	{
		$repository = $this->getPresenter()->getContext()->cms->pageRepository;

		$entity = $repository->find($from);
		$target = $repository->find($to);

		if ($dropmode == "before" || $dropmode == "after") {
			$entity->setParent(
				$target->parent ? : NULL,
				true,
				$dropmode == "after" ? $target : $target->previous
			);
		} else {
			$entity->setParent($target);
		}


		$repository->save($entity);
	}


	public function setFileParent($from, $to, $dropmode)
	{
		$dirRepository = $this->getPresenter()->getContext()->cms->dirRepository;
		$fileRepository = $this->getPresenter()->getContext()->cms->fileRepository;

		$fromType = substr($from, 0, 1);
		$from = substr($from, 2);

		$toType = substr($to, 0, 1);
		$to = substr($to, 2);

		$entity = $fromType == 'd' ? $dirRepository->find($from) : $fileRepository->find($from);
		$target = $toType == 'd' ? $dirRepository->find($to) : $fileRepository->find($to);

		if ($dropmode == "before" || $dropmode == "after") {
			$entity->setParent(
				$target->parent ? : NULL,
				true,
				$dropmode == "after" ? $target : $target->previous
			);
		} else {
			$entity->setParent($target);
		}

		$this->presenter->context->entityManager->flush();
	}
}
