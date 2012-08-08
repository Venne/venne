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
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreFlushEventArgs;
use CmsModule\Content\Entities\BaseFileEntity;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class FileListener implements EventSubscriber
{

	/** @var string */
	protected $publicDir;

	/** @var string */
	protected $protectedDir;

	/** @var string */
	protected $publicUrl;


	/**
	 * @param $publicDir
	 * @param $publicUrl
	 * @param $protectedDir
	 */
	function __construct(\Nette\DI\Container $container)
	{
		$this->publicDir = $container->parameters['wwwDir'] . '/public/media';
		$this->publicUrl = $container->parameters['basePath'] . '/public/media';
		$this->protectedDir = $container->parameters['dataDir'] . '/media';
	}


	/**
	 * Array of events.
	 *
	 * @return array
	 */
	public function getSubscribedEvents()
	{
		return array(
			Events::postLoad,
			Events::preFlush,
		);
	}

	/**
	 * preFlush event.
	 *
	 * @param PreFlushEventArgs $args
	 */
	public function preFlush(PreFlushEventArgs $args)
	{
		$em = $args->getEntityManager();
		$uow = $em->getUnitOfWork();

		foreach ($uow->getScheduledEntityInsertions() as $entity) {
			if ($entity instanceof \CmsModule\Content\Entities\BaseFileEntity) {
				$this->setup($entity);
			}
		}
	}


	/**
	 * postLoad event.
	 *
	 * @param LifecycleEventArgs $args
	 */
	public function postLoad(LifecycleEventArgs $args)
	{
		$entity = $args->getEntity();

		if ($entity instanceof \CmsModule\Content\Entities\BaseFileEntity) {
			$this->setup($entity);
		}
	}


	protected function setup(BaseFileEntity $entity)
	{
		$entity->setPublicDir($this->publicDir);
		$entity->setPublicUrl($this->publicUrl);
		$entity->setProtectedDir($this->protectedDir);
	}
}
