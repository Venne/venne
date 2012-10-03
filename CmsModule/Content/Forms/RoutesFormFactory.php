<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace CmsModule\Content\Forms;

use Venne;
use Venne\Forms\Form;
use DoctrineModule\Forms\FormFactory;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class RoutesFormFactory extends FormFactory
{

	/**
	 * @param Form $form
	 */
	public function configure(Form $form)
	{

		$form->addMany('routes', function (\Nette\Forms\Container $container) use ($form) {
			$container->setCurrentGroup($group = $container->getForm()->addGroup('Route: ' . $container->data->url));
			$container->addText('title', 'Title');
			$container->addText('keywords', 'Keywords');
			$container->addText('description', 'Description');
			$container->addText('author', 'Author');
			$container->addSelect('robots', 'Robots')->setItems(\CmsModule\Content\Entities\RouteEntity::getRobotsValues(), FALSE);
			$container->addSelect('changefreq', 'Change freqency')->setItems(\CmsModule\Content\Entities\RouteEntity::getChangefreqValues(), FALSE)->setPrompt('-------');
			$container->addSelect('priority', 'Priority')->setItems(\CmsModule\Content\Entities\RouteEntity::getPriorityValues(), FALSE)->setPrompt('-------');

			// layout
			$container->setCurrentGroup($form->addGroup());
			$container->addCheckbox('copyLayoutFromParent', 'Layout from parent');
			$container['copyLayoutFromParent']->addCondition($form::EQUAL, false)->toggle('group-layout_' . $container->data->id);

			$container->setCurrentGroup($container->getForm()->addGroup()->setOption('id', 'group-layout_' . $container->data->id));
			$container->addSelect('layout', 'Layout', $container->form->presenter->context->cms->scannerService->getLayoutFiles())->setPrompt('-------');

			$container->setCurrentGroup($form->addGroup());
			$container->addCheckbox('copyLayoutToChildren', 'Share layout with children');
			$container['copyLayoutToChildren']->addCondition($form::EQUAL, false)->toggle('group-layout2_' . $container->data->id);

			$container->setCurrentGroup($container->getForm()->addGroup()->setOption('id', 'group-layout2_' . $container->data->id));
			$container->addSelect('childrenLayout', 'Share new layout', $container->form->presenter->context->cms->scannerService->getLayoutFiles())->setPrompt('-------');

			// cache
			$container->setCurrentGroup($form->addGroup());
			$container->addCheckbox('copyCacheModeFromParent', 'Cache mode from parent');
			$container['copyCacheModeFromParent']->addCondition($form::EQUAL, false)->toggle('group-cache_' . $container->data->id);

			$container->setCurrentGroup($container->getForm()->addGroup()->setOption('id', 'group-cache_' . $container->data->id));
			$container->addSelect('cacheMode', 'Cache strategy')->setItems(\CmsModule\Content\Entities\RouteEntity::getCacheModes(), FALSE)->setPrompt('off');
		});

		$form->setCurrentGroup();

		$form->addSaveButton('Save');
	}
}
