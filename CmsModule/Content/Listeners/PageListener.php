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

use CmsModule\Content\Entities\LanguageEntity;
use CmsModule\Content\Entities\RouteEntity;
use CmsModule\Content\Repositories\LanguageRepository;
use Doctrine\Common\EventArgs;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Events;
use Nette\Caching\Cache;
use Nette\Caching\IStorage;
use Nette\DI\Container;
use Nette\InvalidArgumentException;

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
	protected $languageEntity;

	/** @var array */
	protected $entities = array(
		'CmsModule\Content\Entities\PageEntity' => TRUE,
		'CmsModule\Content\Entities\RouteEntity' => TRUE,
		'CmsModule\Content\Entities\ExtendedPageEntity' => TRUE,
		'CmsModule\Content\Entities\ExtendedRouteEntity' => TRUE,
		'CmsModule\Content\Entities\ElementEntity' => TRUE,
		'CmsModule\Pages\Tags\TagEntity' => TRUE,
		'CmsModule\Content\Entities\PermissionEntity' => TRUE,
	);


	/**
	 * @param LanguageEntity $locale
	 */
	public function setLocale($locale = NULL)
	{
		$this->locale = $locale;
		$this->languageEntity = NULL;
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
			Events::loadClassMetadata,
			Events::postLoad,
		);
	}


	/**
	 * @param LifecycleEventArgs $args
	 */
	public function postLoad(LifecycleEventArgs $args)
	{
		if (($e = $this->getLanguageEntity()) && ($entity = $args->getEntity()) instanceof RouteEntity) {
			$entity->setLocale($e);
		}
	}


	/**
	 * @return LanguageEntity
	 */
	private function getLanguageEntity()
	{
		if (!$this->languageEntity) {
			$this->languageEntity = $this->locale instanceof LanguageEntity ? $this->locale : $this->getLanguageRepository()->findOneBy(array('alias' => $this->locale));
		}
		return $this->languageEntity;
	}


	private $_l = FALSE;


	/**
	 * @param LoadClassMetadataEventArgs $args
	 */
	public function loadClassMetadata(LoadClassMetadataEventArgs $args)
	{
		$meta = $args->getClassMetadata();

		if ($this->_l) {
			if (is_subclass_of($meta->name, 'CmsModule\Content\Entities\ExtendedPageEntity')) {
				if ($meta->associationMappings['extendedMainRoute']['targetEntity'] === 'CmsModule\Blank') {
					$meta->associationMappings['extendedMainRoute']['targetEntity'] = $this->_l;
				}
			} elseif (is_subclass_of($meta->name, 'CmsModule\Content\Entities\ExtendedRouteEntity')) {
				if ($meta->associationMappings['extendedPage']['targetEntity'] === 'CmsModule\Blank') {
					$meta->associationMappings['extendedPage']['targetEntity'] = $this->_l;
				}
			}
			return;
		}

		if (is_subclass_of($meta->name, 'CmsModule\Content\Entities\ExtendedPageEntity')) {
			$em = $args->getEntityManager();
			$mainRouteEntityName = $this->getMainRouteByPage($meta->name);

			$this->_l = $meta->name;
			$routeMeta = $em->getClassMetadata($mainRouteEntityName);
			$this->_l = FALSE;

			$meta->associationMappings['extendedMainRoute']['targetEntity'] = $mainRouteEntityName;
			$routeMeta->associationMappings['extendedPage']['targetEntity'] = $meta->name;
		} else if (is_subclass_of($meta->name, 'CmsModule\Content\Entities\ExtendedRouteEntity')) {
			$em = $args->getEntityManager();
			$pageEntityName = $this->getPageByRoute($meta->name);

			$this->_l = $meta->name;
			$pageMeta = $em->getClassMetadata($pageEntityName);
			$this->_l = FALSE;

			$meta->associationMappings['extendedPage']['targetEntity'] = $pageEntityName;

			$r = $this->getMainRouteByPage($pageEntityName);

			if ($r === $meta->name) {
				$pageMeta->associationMappings['extendedMainRoute']['targetEntity'] = $meta->name;
			}
		}
	}


	/**
	 * @param $class
	 * @return string
	 * @throws \Nette\InvalidArgumentException
	 */
	private function getMainRouteByPage($class)
	{
		if (($ret = $class::getMainRouteName()) === NULL) {
			throw new InvalidArgumentException("Entity '{$class}' must implemented method 'getMainRouteName'.");
		}
		if (!class_exists($ret)) {
			throw new InvalidArgumentException("Class '{$ret}' does not exist.");
		}
		if (!is_subclass_of($ret, 'CmsModule\Content\Entities\ExtendedRouteEntity')) {
			throw new InvalidArgumentException("Method 'getMainRouteName' on '{$class}' must return subclass of 'CmsModule\Content\Entities\ExtendedRouteEntity'. '{$ret}' is given.");
		}
		return $ret;
	}


	/**
	 * @param $class
	 * @return string
	 * @throws \Nette\InvalidArgumentException
	 */
	private function getPageByRoute($class)
	{
		if (($ret = $class::getPageName()) === NULL) {
			throw new InvalidArgumentException("Entity '{$class}' must implemented method 'getPageName'.");
		}
		if (!class_exists($ret)) {
			throw new InvalidArgumentException("Class '{$ret}' does not exist.");
		}
		if (!is_subclass_of($ret, 'CmsModule\Content\Entities\ExtendedPageEntity')) {
			throw new InvalidArgumentException("Method 'getPageName' on '{$class}' must return subclass of 'CmsModule\Content\Entities\ExtendedPageEntity'. '{$ret}' is given.");
		}
		return $ret;
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
