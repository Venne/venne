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

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Events;
use Nette\Caching\Cache;
use Nette\Caching\Storages\FileStorage;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class PageListener implements EventSubscriber
{

	/** @var Cache */
	protected $cache;

	/** @var array */
	protected $entities = array(
		'CmsModule\Content\Entities\PageEntity' => TRUE,
		'CmsModule\Content\Entities\RouteEntity' => TRUE,
		'CmsModule\Content\Entities\ElementEntity' => TRUE,
	);


	/**
	 * @param FileStorage $storage
	 */
	function __construct(FileStorage $storage)
	{
		$this->cache = new Cache($storage);
	}


	/**
	 * Array of events.
	 *
	 * @return array
	 */
	public function getSubscribedEvents()
	{
		return array(Events::onFlush);
	}


	/**
	 * @param OnFlushEventArgs $eventArgs
	 */
	public function onFlush(OnFlushEventArgs $eventArgs)
	{
		$em = $eventArgs->getEntityManager();
		$uow = $em->getUnitOfWork();

		$entities = $this->entities;
		foreach ($uow->getScheduledEntityInsertions() AS $entity) {
			foreach ($entities as $class => $i) {
				if (is_a($entity, $class)) {
					$this->invalidate($class, $entity);
				}
			}
		}

		foreach ($uow->getScheduledEntityUpdates() AS $entity) {
			foreach ($entities as $class => $i) {
				if (is_a($entity, $class)) {
					$this->invalidate($class, $entity);
				}
			}
		}

		foreach ($uow->getScheduledEntityDeletions() AS $entity) {
			foreach ($entities as $class => $i) {
				if (is_a($entity, $class)) {
					$this->invalidate($class, $entity);
				}
			}
		}
	}


	/**
	 * @param $class
	 */
	protected function invalidate($class, $entity)
	{
		if (defined("\\$class::CACHE")) {
			$this->cache->clean(array(
				Cache::TAGS => $class::CACHE,
			));
		}

		if ($entity instanceof \CmsModule\Content\Entities\PageEntity) {
			$this->cache->clean(array(
				Cache::TAGS => array('page' => $entity->id),
			));
		} elseif ($entity instanceof \CmsModule\Content\Entities\RouteEntity) {
			$this->cache->clean(array(
				Cache::TAGS => array('route' => $entity->id),
			));
		} elseif ($entity instanceof \CmsModule\Content\Entities\ElementEntity) {
			foreach ($entity->layout->routes as $route) {
				$this->cache->clean(array(
					Cache::TAGS => array('route' => $route->id),
				));
			}
		}
	}
}
