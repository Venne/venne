<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace CmsModule\Administration\Presenters;

use CmsModule\Forms\SystemDatabaseFormFactory;
use CmsModule\Module\Installers\CmsInstaller;
use Doctrine\ORM\Tools\SchemaTool;
use Nette\Caching\Storages\MemoryStorage;
use Nette\Loaders\RobotLoader;
use Nette\Reflection\ClassType;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 *
 * @secured
 */
class DatabasePresenter extends BasePresenter
{

	/** @var SystemDatabaseFormFactory */
	protected $databaseForm;


	function injectDatabaseForm(SystemDatabaseFormFactory $databaseForm)
	{
		$this->databaseForm = $databaseForm;
	}


	protected function createComponentSystemDatabaseForm()
	{
		$form = $this->databaseForm->invoke();
		$form->onSuccess[] = $this->save;
		return $form;
	}


	public function save()
	{
		$this->flashMessage("Database settings has been updated", "success");
		$this->redirect('install!');
	}


	public function handleInstall()
	{
		if ($this->context->doctrine->createCheckConnection() && count($this->context->schemaManager->listTables()) == 0) {
			/** @var $em \Doctrine\ORM\EntityManager */
			$em = $this->context->entityManager;
			$tool = new SchemaTool($em);

			$robotLoader = new RobotLoader();
			$robotLoader->setCacheStorage(new MemoryStorage);
			$robotLoader->addDirectory($this->context->parameters['modules']['cms']['path'] . '/CmsModule');
			$robotLoader->register();

			$classes = array();
			foreach ($robotLoader->getIndexedClasses() as $item => $a) {
				$ref = ClassType::from($item);
				if ($ref->hasAnnotation('ORM\Entity')) {
					$classes[] = $em->getClassMetadata('\\' . $item);
				}
			}

			$tool->createSchema($classes);

			/** @var $installer CmsInstaller */
			$installer = $this->context->createInstance('CmsModule\Module\Installers\CmsInstaller');
			$installer->install($this->context->venne->moduleManager->modules['cms']);
		}
		$this->redirect(":Cms:Admin:{$this->getContext()->parameters['administration']['defaultPresenter']}:");
	}
}
