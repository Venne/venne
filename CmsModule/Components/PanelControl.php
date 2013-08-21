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

use CmsModule\Content\ContentManager;
use Nette\Http\SessionSection;
use Venne\Application\UI\Control;
use Venne\Module\TemplateManager;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class PanelControl extends Control
{

	/** @var SessionSection */
	protected $session;

	/** @var TemplateManager */
	protected $templateManager;

	/** @var ContentManager */
	protected $contentManager;


	/**
	 * @param TemplateManager $templateManager
	 * @param SessionSection $session
	 * @param ContentManager $contentManager
	 */
	public function __construct(TemplateManager $templateManager, SessionSection $session, ContentManager $contentManager)
	{
		parent::__construct();

		$this->templateManager = $templateManager;
		$this->session = $session;
		$this->contentManager = $contentManager;
	}


	public function render()
	{
		$this->template->render();
	}


	public function getTab()
	{
		return (int)$this->session->tab;
	}


	public function setTab($tab)
	{
		$this->session->tab = (int)$tab;
	}


	public function setState($id, $state)
	{
		if (!isset($this->session->state)) {
			$this->session->state = array();
		}

		if (!isset($this->session->state[$this->getTab()])) {
			$this->session->state[$this->getTab()] = array();
		}

		$this->session->state[$this->getTab()][$id] = $state;
	}


	public function getState($id)
	{
		return isset($this->session->state[$this->getTab()][$id]) ? $this->session->state[$this->getTab()][$id] : FALSE;
	}


	public function handleTab($tab)
	{
		$this->invalidateControl('content');
		$this->invalidateControl('tabs');
		$this->tab = $tab;
		$this->presenter->payload->url = $this->link('this');
	}


	protected function createComponentBrowser()
	{
		$_this = $this;
		$nullLinkParams = \Venne\Application\UI\Helpers::nullLinkParams($this);
		unset($nullLinkParams['lang']);

		$browser = new BrowserControl;
		$browser->setTemplateConfigurator($this->templateConfigurator);

		if ($this->tab == 0) {
			$browser->setLoadCallback($this->getPages);
			$browser->setDropCallback($this->setPageParent);
			$browser->onClick[] = function ($key) use ($_this, $nullLinkParams) {
				$_this->getPresenter()->ajaxRedirect(':Cms:Admin:Content:edit', array('key' => $key) + $nullLinkParams);
			};
			$browser->onExpand[] = $this->pageExpand;
		} else if ($this->tab == 2) {
			$browser->setLoadCallback($this->getFiles);
			$browser->setDropCallback($this->setFileParent);
			$browser->onClick[] = function ($key) use ($_this, $nullLinkParams) {
				if (substr($key, 0, 2) === 'd:') {
					$_this->getPresenter()->ajaxRedirect(':Cms:Admin:Files:', array('key' => $key) + $nullLinkParams);
				}
			};
			$browser->onExpand[] = $this->fileExpand;
		} else if ($this->tab == 3) {
			$browser->setLoadCallback($this->getLayouts);
			$browser->onClick[] = function ($key) use ($_this, $nullLinkParams) {
				$_this->getPresenter()->ajaxRedirect(':Cms:Admin:Layouts:', array('table-floorId' => $key, 'table-floor' => 'Borec') + $nullLinkParams);
			};
			$browser->onExpand[] = $this->layoutExpand;
		} else if ($this->tab == 4) {
			$browser->setLoadCallback($this->getTemplates);
			$browser->onClick[] = function ($key) use ($_this, $nullLinkParams) {
				if (substr($key, 0, 1) === '@') {
					$_this->getPresenter()->ajaxRedirect(':Cms:Admin:Templates:edit', array('key' => $key) + $nullLinkParams);
				}
			};
			$browser->onExpand[] = $this->templateExpand;
		}
		return $browser;
	}


	public function templateExpand($key, $open)
	{
		$this->setState($key, $open);
	}


	public function layoutExpand($key, $open)
	{
		$this->setState($key, $open);
	}


	public function getLayouts($parent = NULL)
	{
		$this->setState($parent, TRUE);

		$data = array();
		$repository = $this->getPresenter()->getContext()->cms->layoutRepository;

		foreach ($repository->findAll() as $entity) {
			$item = array('title' => $entity->name, 'key' => $entity->id);

			$data[] = $item;
		}

		return $data;
	}


	public function getTemplates()
	{
		$data = array();

		foreach ($this->presenter->context->parameters['modules'] as $moduleName => $val) {
			if (!count($this->templateManager->getLayoutsByModule($moduleName))) {
				continue;
			}

			$item = array('title' => $moduleName, 'key' => $moduleName, 'isFolder' => TRUE, 'isLazy' => TRUE);

			if ($this->getState($moduleName)) {
				$item['expand'] = TRUE;
			}

			$data2 = array();
			foreach ($this->templateManager->getLayoutsByModule($moduleName) as $name => $key) {
				$s = array('title' => '@' . $name . ' <small class="muted">' . $this->template->translate('layout') . '</small>', 'key' => $key);

				foreach ($this->templateManager->getTemplatesByModule($moduleName, $name) as $name => $key) {
					$item2 = array('title' => $name . ' <small class="muted">' . $this->template->translate('template') . '</small>', 'key' => $key);
					$s['children'][] = $item2;
				}

				if ($this->getState($key)) {
					$s['expand'] = TRUE;
				}

				$data2[] = $s;
			}


			foreach ($this->templateManager->getTemplatesByModule($moduleName) as $name => $key) {
				$data2[] = array('title' => $name . ' <small class="muted">' . $this->template->translate('template') . '</small>', 'key' => $key);
			}

			$item['children'] = $data2;
			$data[] = $item;
		}

		return $data;
	}


	public function pageExpand($key, $open)
	{
		$this->setState((int)$key, $open);
	}


	public function getPages($parent = NULL)
	{
		$this->setState((int)$parent, TRUE);

		$repository = $this->getPresenter()->getContext()->cms->pageRepository;
		$data = array();

		$dql = $repository->createQueryBuilder('a');
		if ($parent) {
			$dql = $dql->andWhere('a.parent = ?1')->setParameter(1, $parent);
		} else {
			$dql = $dql->andWhere('a.parent IS NULL');
		}
		$dql
			->orderBy('a.position');

		$types = $this->contentManager->getContentTypes();
		foreach ($dql->getQuery()->getResult() as $page) {
			$type = $this->presenter->template->translate($types[$page->class]);
			$item = array('title' => $page->mainRoute->name . ' <small class="muted">' . $type . '</small>', 'key' => $page->id);

			if (count($page->children) > 0) {
				$item['isLazy'] = TRUE;
			}

			if (!$page->parent || $this->getState((int)$page->id)) {
				$item['expand'] = TRUE;
				$item['children'] = $this->getPages($page->id);
			}

			$data[] = $item;
		}
		return $data;
	}


	public function fileExpand($key, $open)
	{
		$key = $key ? substr($key, 2) : NULL;
		$this->setState((int)$key, $open);
	}


	public function getFiles($parent = NULL)
	{
		$parent = $parent ? substr($parent, 2) : NULL;

		$this->setState((int)$parent, TRUE);

		$dirRepository = $this->getPresenter()->getContext()->cms->dirRepository;
		$fileRepository = $this->getPresenter()->getContext()->cms->fileRepository;
		$data = array();

		$dql = $dirRepository->createQueryBuilder('a');
		if ($parent) {
			$dql = $dql->andWhere('a.parent = ?1')->setParameter(1, $parent);
		} else {
			$dql = $dql->andWhere('a.parent IS NULL');
		}
		$dql = $dql->andWhere('a.invisible = :invisible')->setParameter('invisible', FALSE);

		foreach ($dql->getQuery()->getResult() as $page) {
			$item = array('title' => $page->name, 'key' => 'd:' . $page->id);

			$item['isFolder'] = TRUE;

			if (count($page->children) > 0 || count($page->files) > 0) {
				$item['isLazy'] = TRUE;
			}

			if ($this->getState($page->id)) {
				$item['expand'] = TRUE;
				$item['children'] = $this->getFiles('d:' . $page->id);
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
			$item = array('title' => $page->name, 'key' => 'f:' . $page->id);
			$data[] = $item;
		}

		return $data;
	}


	public function setPageParent($from, $to, $dropmode)
	{
		$repository = $this->getPresenter()->getContext()->cms->pageRepository;

		$entity = $repository->find($from);
		$target = $repository->find($to);

		if ($target->parent === NULL && ($dropmode == 'before' || $dropmode == 'after')) {
			$entity->setAsRoot();
			$repository->save($target);
		} else {
			if ($dropmode == 'before' || $dropmode == 'after') {
				$entity->setParent(
					$target->parent ? : NULL,
					TRUE,
					$dropmode == 'after' ? $target : $target->previous
				);
			} else {
				$entity->setParent($target);
			}
			$repository->save($entity);
		}
		$this->invalidateControl('content');
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
				TRUE,
				$dropmode == "after" ? $target : $target->previous
			);
		} else {
			$entity->setParent($target);
		}

		$this->presenter->context->entityManager->flush();
	}
}
