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

use Gedmo\Translatable\TranslatableListener;
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

	/** @persistent */
	public $backlink;

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
		foreach ($this->context->eventManager->getListeners() as $event => $listeners) {
			foreach ($listeners as $hash => $listener) {
				if ($listener instanceof TranslatableListener) {
					$langId = (string)$this->context->cms->languageRepository->findOneBy(array('short' => $this->lang))->id;
					$listener->setTranslatableLocale($langId);
					$listener->setTranslationFallback(true);
					break;
				}
			}
		}
		$this->context->entityManager->refresh($this->page);
	}


	/**
	 * Redirect to other language.
	 *
	 * @param string $alias
	 */
	public function handleChangeLanguage($alias)
	{
		if ($this->page->isInLanguageAlias($alias)) {
			$this->redirect('this', array('lang' => $alias));
		}

		if (!$page = $this->page->getPageWithLanguageAlias($alias)) {
			$page = $this->page;
			do {
				if ($p = $page->getPageWithLanguageAlias($alias)) {
					$this->redirect('this', array('route' => $p->mainRoute, 'lang' => $alias));
				}
				$page = $page->parent;
			} while ($page);
			throw new BadRequestException;
		}

		$this->redirect('this', array('route' => $page->mainRoute, 'lang' => $alias));
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
			$component->setName($name[0]);
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
			$presenter = $this;
			$templateCache = $this->_templateCache;
			$key = $this->getHttpRequest()->getUrl()->getAbsoluteUrl() . ($this->getUser()->isLoggedIn() ? '|logged' : '');
			$this->getApplication()->onResponse[] = function () {
				ob_start();
			};
			$this->getApplication()->onShutdown[] = function () use ($presenter, $templateCache, $key) {
				$output = ob_get_clean();

				if ($presenter instanceof PagePresenter) {
					$parameters = array(
						Cache::TAGS => array(
							'route' => $presenter->route->id,
							'page' => $presenter->page->id,
						),
					);

					if ($presenter->route->getCacheMode() == RouteEntity::DEFAULT_CACHE_MODE) {
						$cacheMode = $presenter->context->parameters['website']['cacheMode'];
					} else {
						$cacheMode = $presenter->route->getCacheMode();
					}

					if ($cacheMode) {
						if ($cacheMode === RouteEntity::CACHE_MODE_TIME) {
							$parameters[Cache::EXPIRE] = '+ ' . $presenter->context->parameters['website']['cacheValue'] . ' minutes';
						}
						$templateCache->save($key, $output, $parameters);
					}
				}
				echo $output;
			};
		}

		parent::beforeRender();

		$this->template->entity = $this->page;
		$this->template->route = $this->route;
		$this["head"]->setTitle($this->route->title);
		$this["head"]->setRobots($this->route->robots);
	}
}
