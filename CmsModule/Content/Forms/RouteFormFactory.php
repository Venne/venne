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

use CmsModule\Services\ScannerService;
use DoctrineModule\Forms\Mappers\EntityMapper;
use Venne;
use Venne\Forms\Form;
use DoctrineModule\Forms\FormFactory;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class RouteFormFactory extends FormFactory
{
	/** @var \CmsModule\Services\ScannerService */
	protected $scannerService;


	/**
	 * @param \DoctrineModule\Forms\Mappers\EntityMapper $mapper
	 * @param \CmsModule\Services\ScannerService $scannerService
	 */
	public function __construct(EntityMapper $mapper, ScannerService $scannerService)
	{
		parent::__construct($mapper);

		$this->scannerService = $scannerService;
	}


	/**
	 * @param Form $form
	 */
	public function configure(Form $form)
	{
		$form->addGroup();
		$form->addText('localUrl', 'URL');
		$form->addText('title', 'Title');
		$form->addText('keywords', 'Keywords');
		$form->addText('description', 'Description');
		$form->addText('author', 'Author');
		$form->addSelect('robots', 'Robots')->setItems(\CmsModule\Content\Entities\RouteEntity::getRobotsValues(), FALSE);
		$form->addSelect('changefreq', 'Change frequency')->setItems(\CmsModule\Content\Entities\RouteEntity::getChangefreqValues(), FALSE)->setPrompt('-------');
		$form->addSelect('priority', 'Priority')->setItems(\CmsModule\Content\Entities\RouteEntity::getPriorityValues(), FALSE)->setPrompt('-------');

		// layout
		$form->setCurrentGroup($form->addGroup());
		$form->addCheckbox('copyLayoutFromParent', 'Layout from parent');
		$form['copyLayoutFromParent']->addCondition($form::EQUAL, false)->toggle('group-layout_' . $form->data->id);

		$form->setCurrentGroup($form->getForm()->addGroup()->setOption('id', 'group-layout_' . $form->data->id));
		$form->addSelect('layout', 'Layout', $this->scannerService->getLayoutFiles())->setPrompt('-------');

		$form->setCurrentGroup($form->addGroup());
		$form->addCheckbox('copyLayoutToChildren', 'Share layout with children');
		$form['copyLayoutToChildren']->addCondition($form::EQUAL, false)->toggle('group-layout2_' . $form->data->id);

		$form->setCurrentGroup($form->getForm()->addGroup()->setOption('id', 'group-layout2_' . $form->data->id));
		$form->addSelect('childrenLayout', 'Share new layout', $this->scannerService->getLayoutFiles())->setPrompt('-------');

		// cache
		$form->setCurrentGroup($form->addGroup());
		$form->addCheckbox('copyCacheModeFromParent', 'Cache mode from parent');
		$form['copyCacheModeFromParent']->addCondition($form::EQUAL, false)->toggle('group-cache_' . $form->data->id);

		$form->setCurrentGroup($form->getForm()->addGroup()->setOption('id', 'group-cache_' . $form->data->id));
		$form->addSelect('cacheMode', 'Cache strategy')->setItems(\CmsModule\Content\Entities\RouteEntity::getCacheModes(), FALSE)->setPrompt('off');

		$form->setCurrentGroup();
		$form->addSaveButton('Save');
	}
}
