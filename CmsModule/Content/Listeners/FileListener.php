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

use CmsModule\Content\Entities\BaseFileEntity;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreFlushEventArgs;
use Doctrine\ORM\Events;
use Nette\Application\Application;
use Nette\DI\Container;
use Nette\Security\User;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class FileListener implements EventSubscriber
{

	/** @var Container|\SystemContainer */
	protected $container;

	/** @var string */
	protected $publicDir;

	/** @var string */
	protected $protectedDir;

	/** @var string */
	protected $publicUrl;

	/** @var User */
	protected $_user;


	/**
	 * @param Container $container
	 * @param $publicDir
	 * @param $protectedDir
	 * @param $publicUrl
	 */
	public function __construct(Container $container, $publicDir, $protectedDir, $publicUrl)
	{
		$this->container = $container;
		$this->publicDir = $publicDir;
		$this->protectedDir = $protectedDir;
		$this->publicUrl = $container->parameters['basePath'] . $publicUrl;
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


	/**
	 * @param BaseFileEntity $entity
	 */
	protected function setup(BaseFileEntity $entity)
	{
		$entity->setPublicDir($this->publicDir);
		$entity->setPublicUrl($this->publicUrl);
		$entity->setProtectedDir($this->protectedDir);
		$entity->setUser($this->getUser());
	}


	/**
	 * @return User
	 */
	protected function getUser()
	{
		if (!$this->_user) {
			$this->_user = $this->container->getByType('Nette\Security\User');
		}

		return $this->_user;
	}
}
