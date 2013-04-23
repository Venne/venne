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

use CmsModule\Content\Entities\LanguageEntity;
use Nette\Caching\Cache;
use Gedmo\Translatable\TranslatableListener;
use Nette\Application\BadRequestException;
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

	/** @var LanguageEntity */
	private $language;

	/** @var string */
	protected $_layoutPath;

	/** @var \Nette\Caching\Cache */
	protected $_templateCache;

	/** @var Venne\Module\Helpers */
	protected $moduleHelpers;


	public function injectTemplateCache(\Nette\Caching\IStorage $cache)
	{
		$this->_templateCache = new Cache($cache, self::CACHE_OUTPUT);
	}


	/**
	 * @param \Venne\Module\Helpers $moduleHelpers
	 */
	public function injectModulesHelper(\Venne\Module\Helpers $moduleHelpers)
	{
		$this->moduleHelpers = $moduleHelpers;
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
					$listener->setTranslationFallback(TRUE);
					break;
				}
			}
		}
		$this->context->entityManager->refresh($this->page);
	}


	/**
	 * @return LanguageEntity
	 */
	public function getLanguage()
	{
		if (!$this->language) {
			$this->language = $this->context->cms->languageRepository->findOneBy(array('alias' => $this->lang));
		}
		return $this->language;
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
				$this->_layoutPath = FALSE;
			} else {
				$this->_layoutPath = $this->moduleHelpers->expandPath($this->route->getLayout()->getFile(), 'Resources/layouts');
			}
		}

		return $this->_layoutPath === FALSE ? NULL : $this->_layoutPath;
	}


	/**
	 * Formats layout template file names.
	 *
	 * @return array
	 */
	public function formatLayoutTemplateFiles()
	{
		return array($this->getLayoutPath());
	}


	/**
	 * Formats view template file names.
	 *
	 * @return array
	 */
	public function formatTemplateFiles()
	{
		$ret = parent::formatTemplateFiles();
		$presenter = str_replace(":", ".", $this->name);

		$path = dirname($this->getLayoutPath());
		if ($path) {
			$ret = array_merge(array(
				"$path/$presenter.$this->view.latte",
			), $ret);
		}
		return $ret;
	}


	public function createComponent($name)
	{
		if (strpos($name, \CmsModule\Content\ElementManager::ELEMENT_PREFIX) === 0) {
			$name = substr($name, strlen(\CmsModule\Content\ElementManager::ELEMENT_PREFIX));
			$name = explode('_', $name);
			$id = end($name);
			unset($name[count($name) - 1]);
			$name = implode('_', $name);
			/** @var $component \CmsModule\Content\IElement */
			$component = $this->context->cms->elementManager->createInstance($id);
			$component->setRoute($this->route);
			$component->setName($name);
			$component->setLanguage($this->getLanguage());
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
		$this['head']->setTitle($this->route->title);
		$this['head']->setRobots($this->route->robots);
		$this['head']->setKeywords($this->route->keywords);
		$this['head']->setDescription($this->route->description);
		$this['head']->setAuthor($this->route->author);
	}
}
