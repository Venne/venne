<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace CmsModule\Content\Listeners;

use CmsModule\Content\Entities\ExtendedPageEntity;
use CmsModule\Content\Entities\ExtendedRouteEntity;
use CmsModule\Content\Entities\LanguageEntity;
use CmsModule\Content\Entities\PageEntity;
use CmsModule\Content\Entities\RouteEntity;
use CmsModule\Content\Repositories\LanguageRepository;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Events;
use Nette\Caching\Cache;
use Nette\Caching\IStorage;
use Nette\DI\Container;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class PageListener implements EventSubscriber
{

	/** @var Cache */
	protected $cache;

	/** @var LanguageEntity */
	protected $locale;

	/** @var Container */
	protected $container;

	/** @var LanguageEntity */
	protected $languageEntity = FALSE;

	/** @var array */
	protected $entities = array(
		'CmsModule\Content\Entities\PageEntity' => TRUE,
		'CmsModule\Content\Entities\RouteEntity' => TRUE,
		'CmsModule\Content\Entities\RouteTranslationEntity' => TRUE,
		'CmsModule\Content\Entities\ExtendedPageEntity' => TRUE,
		'CmsModule\Content\Entities\ExtendedRouteEntity' => TRUE,
		'CmsModule\Content\Entities\ElementEntity' => TRUE,
		'CmsModule\Content\Entities\LanguageEntity' => TRUE,
		'CmsModule\Pages\Tags\TagEntity' => TRUE,
		'CmsModule\Pages\Rss\RssEntity' => TRUE,
		'CmsModule\Content\Entities\PermissionEntity' => TRUE,
	);


	/**
	 * @param LanguageEntity $locale
	 */
	public function setLocale($locale = NULL)
	{
		$this->locale = $locale;
		$this->languageEntity = FALSE;
	}


	/**
	 * @param IStorage $storage
	 * @param Container $container
	 */
	public function __construct(IStorage $storage, Container $container)
	{
		$this->cache = new Cache($storage);
		$this->container = $container;
	}


	/**
	 * @return LanguageRepository
	 */
	protected function getLanguageRepository()
	{
		return $this->container->getByType('CmsModule\Content\Repositories\LanguageRepository');
	}


	/**
	 * Array of events.
	 *
	 * @return array
	 */
	public function getSubscribedEvents()
	{
		return array(
			Events::onFlush,
			Events::postLoad,
		);
	}


	/**
	 * @param LifecycleEventArgs $args
	 */
	public function postLoad(LifecycleEventArgs $args)
	{
		$entity = $args->getEntity();
		if (($entity instanceof RouteEntity || $entity instanceof ExtendedRouteEntity || $entity instanceof PageEntity || $entity instanceof ExtendedPageEntity) && ($e = $this->getLanguageEntity())) {
			$entity->setLocale($e);
		}
	}


	/**
	 * @return LanguageEntity
	 */
	private function getLanguageEntity()
	{
		if ($this->languageEntity === FALSE) {
			$this->languageEntity = $this->locale instanceof LanguageEntity ? $this->locale : $this->getLanguageRepository()->findOneBy(array('alias' => $this->locale));
		}
		return $this->languageEntity;
	}


	/**
	 * @param OnFlushEventArgs $eventArgs
	 */
	public function onFlush(OnFlushEventArgs $eventArgs)
	{
		$em = $eventArgs->getEntityManager();
		$uow = $em->getUnitOfWork();

		foreach ($uow->getScheduledEntityInsertions() AS $entity) {
			foreach ($this->entities as $class => $i) {
				if (is_a($entity, $class)) {
					$this->invalidate($class, $entity, 'insert');
				}
			}
		}

		foreach ($uow->getScheduledEntityUpdates() AS $entity) {
			foreach ($this->entities as $class => $i) {
				if (is_a($entity, $class)) {
					$this->invalidate($class, $entity, 'update');
				}
			}
		}

		foreach ($uow->getScheduledEntityDeletions() AS $entity) {
			foreach ($this->entities as $class => $i) {
				if (is_a($entity, $class)) {
					$this->invalidate($class, $entity, 'delete');
				}
			}
		}
	}


	/**
	 * @param $class
	 * @param $entity
	 * @param $mode
	 */
	protected function invalidate($class, $entity, $mode)
	{
		if (defined("\\$class::CACHE")) {
			$this->cache->clean(array(
				Cache::TAGS => $class::CACHE,
			));
		}

		if ($entity instanceof \CmsModule\Content\Entities\PageEntity || ($entity instanceof \CmsModule\Content\Entities\ExtendedPageEntity && $entity = $entity->page)) {
			$this->cache->clean(array(
				Cache::TAGS => array('pages', 'page-' . $mode, 'page-' . $entity->id),
			));
		} elseif ($entity instanceof \CmsModule\Content\Entities\RouteEntity || ($entity instanceof \CmsModule\Content\Entities\ExtendedRouteEntity && $entity = $entity->route)) {
			$this->cache->clean(array(
				Cache::TAGS => array('routes', 'route-' . $mode, 'route-' . $entity->id),
			));
		} elseif ($entity instanceof \CmsModule\Content\Elements\ElementEntity || ($entity instanceof \CmsModule\Content\Elements\ExtendedElementEntity && $entity = $entity->element)) {
			$this->cache->clean(array(
				Cache::TAGS => array('elements', 'element-' . $mode, 'element-' . $entity->id),
			));

			if ($entity->mode === $entity::MODE_LAYOUT) {
				foreach ($entity->layout->routes as $route) {
					$this->cache->clean(array(
						Cache::TAGS => array('routes', 'route-update', 'route-' . $route->id),
					));
				}
			} elseif ($entity->mode === $entity::MODE_PAGE) {
				$this->cache->clean(array(
					Cache::TAGS => array('pages', 'page-update', 'page-' . $entity->page->id),
				));
			} elseif ($entity->mode === $entity::MODE_ROUTE) {
				$this->cache->clean(array(
					Cache::TAGS => array('routes', 'route-update', 'page-' . $entity->route),
				));
			} else {
				$this->cache->clean(array(
					Cache::TAGS => array('routes', 'route-update', 'pages', 'page-update'),
				));
			}
		}
	}
}
