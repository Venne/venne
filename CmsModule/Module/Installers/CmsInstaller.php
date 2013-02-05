<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace CmsModule\Module\Installers;

use Venne;
use Nette\Object;
use Nette\DI\Container;
use Venne\Module\IModule;
use Venne\Module\IInstaller;
use CmsModule\Content\Entities\LayoutEntity;
use Doctrine\ORM\EntityManager;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class CmsInstaller extends Object implements IInstaller
{

	/** @var Container */
	protected $context;

	/** @var Venne\Module\TemplateManager */
	protected $templateManager;

	/** @var EntityManager */
	protected $entityManager;


	/**
	 * @param \Nette\DI\Container $context
	 * @param \Venne\Module\TemplateManager $templateManager
	 * @param \Doctrine\ORM\EntityManager $entityManager
	 */
	public function __construct(Container $context, Venne\Module\TemplateManager $templateManager, EntityManager $entityManager)
	{
		$this->context = $context;
		$this->templateManager = $templateManager;
		$this->entityManager = $entityManager;
	}


	/**
	 * @param \Venne\Module\IModule $module
	 */
	public function install(IModule $module)
	{
		if (!$this->context->hasService('doctrine') || !$this->context->doctrine->createCheckConnection()) {
			throw new \Exception('Database connection not found!');
		}

		$layouts = $this->templateManager->getLayoutsByModule($module->getName());
		$repository = $this->getTemplateRepository();

		foreach ($layouts as $path => $name) {
			$origName = $name;
			$name = explode('/', $name);
			$name = $name[0] . ' - ' . $name[count($name) - 2] . ' - default';

			$entity = new LayoutEntity;
			$entity->setName($name);
			$entity->setFile($origName);

			$repository->save($entity);
		}
	}


	/**
	 * @param \Venne\Module\IModule $module
	 */
	public function uninstall(IModule $module)
	{
		if (!$this->context->hasService('doctrine') || !$this->context->doctrine->createCheckConnection()) {
			throw new \Exception('Database connection not found!');
		}

		$layouts = $this->templateManager->getLayoutsByModule($module->getName());
		$repository = $this->getTemplateRepository();

		foreach ($layouts as $path => $name) {
			$name = explode('/', $name);
			$name = $name[0] . ' - ' . $name[count($name) - 2] . ' - default';

			foreach ($repository->findBy(array('file' => $path)) as $entity) {
				$repository->delete($entity);
			}
		}
	}


	/**
	 * @param \Venne\Module\IModule $module
	 * @param $from
	 * @param $to
	 */
	public function upgrade(IModule $module, $from, $to)
	{
	}


	/**
	 * @param \Venne\Module\IModule $module
	 * @param $from
	 * @param $to
	 */
	public function downgrade(IModule $module, $from, $to)
	{
	}


	/**
	 * @return \Doctrine\ORM\EntityRepository
	 */
	protected function getTemplateRepository()
	{
		return $this->entityManager->getRepository('CmsModule\Content\Entities\LayoutEntity');
	}
}

