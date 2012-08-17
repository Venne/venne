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
use Doctrine\ORM\Events;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Nette\Caching\Cache;
use Nette\Caching\Storages\FileStorage;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class PageListener implements EventSubscriber
{

	/** @var Cache */
	protected $cache;

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


	public function onFlush(OnFlushEventArgs $eventArgs)
	{
		$em = $eventArgs->getEntityManager();
		$uow = $em->getUnitOfWork();

		foreach ($uow->getScheduledEntityInsertions() AS $entity) {
			if ($entity instanceof \CmsModule\Content\Entities\PageEntity) {
				$this->invalidateCache();
				return;
			}
		}

		foreach ($uow->getScheduledEntityUpdates() AS $entity) {
			if ($entity instanceof \CmsModule\Content\Entities\PageEntity) {
				$this->invalidateCache();
				return;
			}
		}

		foreach ($uow->getScheduledEntityDeletions() AS $entity) {
			if ($entity instanceof \CmsModule\Content\Entities\PageEntity) {
				$this->invalidateCache();
				return;
			}
		}
	}


	protected function invalidateCache()
	{
		$this->cache->clean(array(
			Cache::TAGS => \CmsModule\Content\Entities\PageEntity::CACHE,
		));
	}


}
