<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace CmsModule;

use Nette\Config\Compiler;
use Nette\Config\Configurator;
use Nette\DI\Container;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class Module extends \CmsModule\Module\Module
{


	/** @var string */
	protected $version = "2.0";

	/** @var string */
	protected $description = "Cms module for Venne:CMS";


	public function compile(Compiler $compiler)
	{
		$compiler->addExtension($this->getName(), new \CmsModule\DI\CmsExtension($this->getPath(), $this->getNamespace()));
	}


	public function install(\Nette\DI\Container $container)
	{
		parent::install($container);

		// Create db structure
		if ($container->createCheckConnection()) {
			$em = $container->entityManager;
			$tool = new \Doctrine\ORM\Tools\SchemaTool($em);

			// Install default roles
			$repository = $em->getRepository('\CmsModule\Security\Entities\RoleEntity');
			foreach (array("admin" => NULL, "guest" => NULL, "authenticated" => "guest") as $name => $parent) {
				$role = $repository->createNew();
				$role->name = $name;
				if ($parent) {
					$role->parent = $repository->findOneBy(array("name" => $parent));
				}
				$repository->save($role);
			}
		}
	}


	protected function getConfigArray()
	{
		return array(
			'parameters' => array(
				'administration' => array(
					'login' => array(
						'name' => '',
						'password' => ''),

					'routePrefix' => 'admin'
				),
				'website' => array(
					'title' => 'Blog %s %t',
					'titleSeparator' => '|',
					'keywords' => '',
					'description' => '',
					'author' => '',
					'routePrefix' => '',
					'languages' => array(),
					'defaultLanguage' => 'cs',
					'defaultPresenter' => 'Homepage',
					'errorPresenter' => 'Error'
				)
			)
		);
	}

}
