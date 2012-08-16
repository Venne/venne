<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace CmsModule\Content;

use DoctrineModule\Forms\Mapping\EntityFormMapper;
use Doctrine\ORM\EntityManager;
use AssetsModule\Managers\AssetManager;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class RoutesForm extends Form
{

	/**
	 * Application form constructor.
	 */
	public function create()
	{
		$this->addMany('routes', function(\Nette\Forms\Container $container)
		{
			$container->setCurrentGroup($container->getForm()->addGroup('Route' . $container->entity->url));
			$container->addText('title', 'Title');
			$container->addText('keywords', 'Keywords');
			$container->addText('description', 'Description');
			$container->addText('author', 'Author');
			$container->addSelect('robots', 'Robots', \CmsModule\Content\Entities\RouteEntity::$robotsValues);
			$container->addSelect('layout', 'Layout', $container->form->presenter->context->cms->scannerService->getLayoutFiles())->setPrompt('-------');
		});

		$this->setCurrentGroup();
	}



}
