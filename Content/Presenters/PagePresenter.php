<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace CmsModule\Content\Presenters;

use Venne;
use CmsModule\Routes\Page as PageRoute;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class PagePresenter extends \CmsModule\Presenters\FrontPresenter
{


	/** @persistent */
	public $url = "";

	/** @persistent */
	public $cmsPage;

	/** @var \CmsModule\Entities\PageEntity */
	public $page;

	/** @var \CmsModule\Entities\RouteEntity */
	public $route;


	/**
	 * @return void
	 */
	public function startup()
	{
		parent::startup();

		$this->invalidLinkMode = self::INVALID_LINK_WARNING;

		$this->route = $this->getParameter("route");
		$this->page = $this->route->getPage();

		// load page module entity
		if (!$this->page || !$this->route) {
			throw new \Nette\Application\BadRequestException;
		}

		$this->url = $this->route->url;
	}


	/**
	 * Redirect to other language.
	 *
	 * @param string $alias
	 */
	public function handleChangeLanguage($alias)
	{
		$page = $this->page->getPageWithLanguageAlias($alias);
		$this->redirect("this", array(
			"lang" => $alias,
			"url" => ($page ? $page->url : "")
		));
	}


	/**
	 * Get layout path.
	 *
	 * @return null|string
	 */
	protected function getLayoutPath()
	{
		if (!$this->route->layout) {
			return NULL;
		}

		$pos = strpos($this->route->layout, "/");
		$module = lcfirst(substr($this->route->layout, 1, $pos - 1));

		if (!$this->context->hasService($module)) {
			return NULL;
		}

		return $this->context->$module->getPath() . "/layouts/" . substr($this->route->layout, $pos + 1);
	}


	/**
	 * Formats layout template file names.
	 *
	 * @return array
	 */
	public function formatLayoutTemplateFiles()
	{
		$path = $this->getLayoutPath();
		return ($path ? array($path . "/@layout.latte") : parent::formatLayoutTemplateFiles());
	}


	/**
	 * Formats view template file names.
	 *
	 * @return array
	 */
	public function formatTemplateFiles()
	{
		$ret = parent::formatTemplateFiles();
		$name = $this->getName();
		$presenter = str_replace(":", "/", $this->name);

		$path = $this->getLayoutPath();
		if ($path) {
			$ret = array_merge(array(
				"$path/$presenter/$this->view.latte",
				"$path/$presenter.$this->view.latte",
			), $ret);
		}
		return $ret;
	}


	/**
	 * Component factory. Delegates the creation of components to a createComponent<Name> method.
	 *
	 * @param  string      component name
	 * @return IComponent  the created component (optionally)
	 */
	public function createComponent($name)
	{
		if (substr($name, 0, 17) == "contentExtension_") {
			$this->context->eventManager->dispatchEvent(\Venne\ContentExtension\Events::onContentExtensionRender);
		} else {
			return parent::createComponent($name);
		}
	}


	/**
	 * Common render method.
	 *
	 * @return void
	 */
	public function beforeRender()
	{
		parent::beforeRender();

		$this->template->entity = $this->page;
		$this["head"]->setTitle($this->route->title);
		$this["head"]->setRobots($this->route->robots);
	}

}

