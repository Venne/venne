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
use Nette\Caching\Cache;
use CmsModule\Content\Entities\RouteEntity;
use CmsModule\Content\Entities\PageEntity;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class PagePresenter extends \CmsModule\Presenters\FrontPresenter
{

	const CACHE_OUTPUT = 'Venne.Output';

	/** @var PageEntity */
	public $page;

	/**
	 * @persistent
	 * @var RouteEntity
	 */
	public $route;

	/** @var string */
	protected $_layoutPath;

	/** @var \Nette\Caching\Cache */
	protected $_templateCache;


	public function injectTemplateCache(\Nette\Caching\IStorage $cache)
	{
		$this->_templateCache = new Cache($cache, self::CACHE_OUTPUT);
	}


	/**
	 * @return void
	 */
	public function startup()
	{
		parent::startup();

		if (!$this->route) {
			throw new \Nette\Application\BadRequestException;
		}
		$this->page = $this->route->getPage();
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
			$this->redirect('this', array('route' => NULL, 'url' => '', 'lang' => $alias));
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


	public function createComponent($name)
	{
		if (strpos($name, \CmsModule\Content\ElementManager::ELEMENT_PREFIX) === 0) {
			$name = substr($name, strlen(\CmsModule\Content\ElementManager::ELEMENT_PREFIX));
			$name = explode('_', $name, 2);
			/** @var $component \CmsModule\Content\IElement */
			$component = $this->context->cms->elementManager->createInstance($name[1]);
			$component->setRoute($this->route);
			$component->setKey($name[0]);
			return $component;
		}

		return parent::createComponent($name);
	}


	/**
	 * Common render method.
	 *
	 * @return void
	 */
	public function beforeRender()
	{
		// Cache all page
		if (!$this->isAuthorized(':Cms:Admin:Panel:') && $this->route->getCacheMode()) {
			$this->getApplication()->onResponse[] = function()
			{
				ob_start();
			};
			$this->getApplication()->onShutdown[] = function()
			{
				$output = ob_get_clean();

				$parameters = array(
					Cache::TAGS => array('url' => $this->getHttpRequest()->getUrl()->getAbsoluteUrl()),
				);
				if ($this->route->getCacheMode() === RouteEntity::CACHE_MODE_TIME || ($this->route->getCacheMode() == RouteEntity::DEFAULT_CACHE_MODE && $this->context->parameters['website']['cacheMode'] === RouteEntity::CACHE_MODE_TIME)) {
					$parameters[Cache::EXPIRE] = '+ ' . $this->context->parameters['website']['cacheValue'] . ' minutes';
				}

				$this->_templateCache->save($this->getHttpRequest()->getUrl()->getAbsoluteUrl(), $output, $parameters);
				echo $output;
			};
		}

		parent::beforeRender();

		$this->template->entity = $this->page;
		$this["head"]->setTitle($this->route->title);
		$this["head"]->setRobots($this->route->robots);
	}
}
