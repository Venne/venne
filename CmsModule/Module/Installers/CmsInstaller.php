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

use CmsModule\Content\Entities\LayoutEntity;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Nette\DI\Container;
use Nette\Object;
use Venne\Module\IInstaller;
use Venne\Module\IModule;
use Venne\Module\TemplateManager;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class CmsInstaller extends Object implements IInstaller
{

	/** @var Container */
	protected $context;

	/** @var TemplateManager */
	protected $templateManager;

	/** @var EntityManager */
	protected $entityManager;


	/**
	 * @param Container $context
	 * @param TemplateManager $templateManager
	 * @param EntityManager $entityManager
	 */
	public function __construct(Container $context, TemplateManager $templateManager, EntityManager $entityManager)
	{
		$this->context = $context;
		$this->templateManager = $templateManager;
		$this->entityManager = $entityManager;
	}


	/**
	 * @param IModule $module
	 */
	public function install(IModule $module)
	{
		if (!$this->context->hasService('doctrine') || !$this->context->doctrine->createCheckConnection()) {
			throw new \Exception('Database connection not found!');
		}

		$layouts = $this->templateManager->getLayoutsByModule($module->getName());
		$repository = $this->getTemplateRepository();

		foreach ($layouts as $name) {
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
	 * @param IModule $module
	 */
	public function uninstall(IModule $module)
	{
		if (!$this->context->hasService('doctrine') || !$this->context->doctrine->createCheckConnection()) {
			throw new \Exception('Database connection not found!');
		}

		$layouts = $this->templateManager->getLayoutsByModule($module->getName());
		$repository = $this->getTemplateRepository();

		foreach ($layouts as $path => $name) {
			foreach ($repository->findBy(array('file' => $path)) as $entity) {
				$repository->delete($entity);
			}
		}
	}


	/**
	 * @param IModule $module
	 * @param $from
	 * @param $to
	 */
	public function upgrade(IModule $module, $from, $to)
	{
	}


	/**
	 * @param IModule $module
	 * @param $from
	 * @param $to
	 */
	public function downgrade(IModule $module, $from, $to)
	{
	}


	/**
	 * @return EntityRepository
	 */
	protected function getTemplateRepository()
	{
		return $this->entityManager->getRepository('CmsModule\Content\Entities\LayoutEntity');
	}
}

