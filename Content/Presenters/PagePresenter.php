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
use CmsModule\Content\Routes\PageRoute;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class PagePresenter extends \CmsModule\Presenters\FrontPresenter
{


	/** @var \CmsModule\Content\Entities\PageEntity */
	public $page;

	/**
	 * @persistent
	 * @var \CmsModule\Content\Entities\RouteEntity
	 */
	public $route;

	/** @var string */
	protected $_layoutPath;


	/**
	 * @return void
	 */
	public function startup()
	{
		parent::startup();

		$this->invalidLinkMode = self::INVALID_LINK_WARNING;

		$this->route = $this->getParameter("route");
		$this->page = $this->route->getPage();

		if (!$this->page || !$this->route) {
			throw new \Nette\Application\BadRequestException;
		}
	}


	/**
	 * Redirect to other language.
	 *
	 * @param string $alias
	 */
	public function handleChangeLanguage($alias)
	{
		$page = $this->page->getPageWithLanguageAlias($alias);

		try {
			$this->redirect('this', array('route' => $page->mainRoute, 'lang' => $alias));
		} catch (\Nette\Application\UI\InvalidLinkException $e) {
			$this->redirectUrl($this->template->basePath);
		}
	}


	/**
	 * Get layout path.
	 *
	 * @return null|string
	 */
	public function getLayoutPath()
	{
		if ($this->_layoutPath === NULL) {
			if (!$this->route->layout) {
				$this->_layoutPath = false;
			}

			if ($this->route->layout == 'default') {
				$layout = $this->context->parameters['website']['layout'];
			} else {
				$layout = $this->route->layout;
			}

			$pos = strpos($layout, "/");
			$module = lcfirst(substr($layout, 1, $pos - 1));

			if ($module === 'app') {
				$this->_layoutPath = $this->context->parameters['appDir'] . '/layouts/' . substr($layout, $pos + 1);
			} else if (!isset($this->context->parameters['modules'][$module]['path'])) {
				$this->_layoutPath = false;
			} else {
				$this->_layoutPath = $this->context->parameters['modules'][$module]['path'] . "/layouts/" . substr($layout, $pos + 1);
			}
		}

		return $this->_layoutPath === false ? NULL : $this->_layoutPath;
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

