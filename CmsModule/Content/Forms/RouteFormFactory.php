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

use CmsModule\Content\Entities\RouteEntity;
use DoctrineModule\Forms\FormFactory;
use FormsModule\ControlExtensions\ControlExtension;
use Venne\Forms\Form;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class RouteFormFactory extends FormFactory
{

	protected function getControlExtensions()
	{
		return array_merge(parent::getControlExtensions(), array(
			new ControlExtension,
			new \CmsModule\Content\Forms\ControlExtensions\ControlExtension,
		));
	}


	/**
	 * @param Form $form
	 */
	public function configure(Form $form)
	{
		$form->addGroup();
		$form->addText('name', 'Name');
		$form->addText('localUrl', 'URL');
		$form->addText('title', 'Title');
		$form->addText('keywords', 'Keywords');
		$form->addText('description', 'Description');
		$form->addManyToOne('author', 'Author');
		$form->addSelect('robots', 'Robots')->setItems(RouteEntity::getRobotsValues(), FALSE);
		$form->addSelect('changefreq', 'Change frequency')->setItems(RouteEntity::getChangefreqValues(), FALSE)->setPrompt('-------');
		$form->addSelect('priority', 'Priority')->setItems(RouteEntity::getPriorityValues(), FALSE)->setPrompt('-------');

		// layout
		$form->setCurrentGroup($form->addGroup());
		$form->addCheckbox('copyLayoutFromParent', 'Layout from parent');
		$form['copyLayoutFromParent']->addCondition($form::EQUAL, FALSE)->toggle('group-layout_' . $form->data->id);

		$form->setCurrentGroup($form->getForm()->addGroup()->setOption('id', 'group-layout_' . $form->data->id));
		$form->addManyToOne('layout', 'Layout');

		$form->setCurrentGroup($form->addGroup());
		$form->addCheckbox('copyLayoutToChildren', 'Share layout with children');
		$form['copyLayoutToChildren']->addCondition($form::EQUAL, FALSE)->toggle('group-layout2_' . $form->data->id);

		$form->setCurrentGroup($form->getForm()->addGroup()->setOption('id', 'group-layout2_' . $form->data->id));
		$form->addManyToOne('childrenLayout', 'Share new layout');

		// cache
		$form->setCurrentGroup($form->addGroup());
		$form->addCheckbox('copyCacheModeFromParent', 'Cache mode from parent');
		$form['copyCacheModeFromParent']->addCondition($form::EQUAL, FALSE)->toggle('group-cache_' . $form->data->id);

		$form->setCurrentGroup($form->getForm()->addGroup()->setOption('id', 'group-cache_' . $form->data->id));
		$form->addSelect('cacheMode', 'Cache strategy')->setItems(\CmsModule\Content\Entities\RouteEntity::getCacheModes(), FALSE)->setPrompt('off');

		$form->addGroup('Dates');
		$form->addDateTime('created', 'Created')->setDisabled(TRUE);
		$form->addDateTime('updated', 'Updated')->setDisabled(TRUE);
		$form->addDateTime('released', 'Released');
		$form->addDateTime('expired', 'Expired');

		$form->addGroup('Tags');
		$form->addContentTags('tags');

		$form->setCurrentGroup();
		$form->addSaveButton('Save');
	}
}
