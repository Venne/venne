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

use CmsModule\Content\Entities\IloggableEntity;
use CmsModule\Content\Entities\LogEntity;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\UnitOfWork;
use Nette\DI\Container;
use Nette\Security\User;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class LogListener implements EventSubscriber
{
	/** @var Container|\SystemContainer */
	protected $container;


	public function __construct(Container $container)
	{
		$this->container = $container;
	}


	/**
	 * @return array
	 */
	public function getSubscribedEvents()
	{
		return array(
			Events::onFlush,
		);
	}


	/**
	 * @param OnFlushEventArgs $eventArgs
	 */
	public function onFlush(OnFlushEventArgs $eventArgs)
	{
		$em = $eventArgs->getEntityManager();
		$uow = $em->getUnitOfWork();

		foreach ($uow->getScheduledEntityInsertions() AS $entity) {
			if ($entity instanceof IloggableEntity) {
				$this->log($uow, $em, $entity, LogEntity::ACTION_CREATED);
			}
		}

		foreach ($uow->getScheduledEntityUpdates() AS $entity) {
			if ($entity instanceof IloggableEntity) {
				$this->log($uow, $em, $entity, LogEntity::ACTION_UPDATED);
			}
		}

		foreach ($uow->getScheduledEntityDeletions() AS $entity) {
			if ($entity instanceof IloggableEntity) {
				$this->log($uow, $em, $entity, LogEntity::ACTION_REMOVED);
			}
		}
	}


	/**
	 * @param UnitOfWork $uow
	 * @param EntityManager $em
	 * @param $entity
	 * @param $action
	 */
	protected function log(UnitOfWork $uow, EntityManager $em, $entity, $action)
	{
		$logEntity = new LogEntity($this->getUser(), get_class($entity), $entity->id, $action);
		$entity->log($logEntity, $uow, $action);

		$uow->persist($logEntity);
		$uow->computeChangeSet($em->getClassmetadata(get_class($logEntity)), $logEntity);
	}


	protected function getUser()
	{
		return $this->container->cms->userRepository->findOneBy(array('email' => $this->container->user->getIdentity()->getId()));
	}
}
