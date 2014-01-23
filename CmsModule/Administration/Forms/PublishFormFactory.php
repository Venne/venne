<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace CmsModule\Administration\Forms;

use DoctrineModule\Forms\FormFactory;
use FormsModule\ControlExtensions\ControlExtension;
use Venne\Forms\Form;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class PublishFormFactory extends FormFactory
{

	protected function getControlExtensions()
	{
		return array_merge(parent::getControlExtensions(), array(
			new ControlExtension,
		));
	}


	/**
	 * @param Form $form
	 */
	public function configure(Form $form)
	{
		$page = $form->addOne('page');
		$mainRoute = $page->addOne('mainRoute');
		$mainRoute->setCurrentGroup($form->addGroup());

		$mainRoute->addCheckbox('published', 'Publish');
		$mainRoute->addDateTime('released', 'Release')
			->addRule($form::FILLED);
		$mainRoute->addDateTime('expired', 'Expire');

		$form->setCurrentGroup();
		$form->addSaveButton('Save');
	}


	public function handleSave(Form $form)
	{
		$form->data->page->published = $form->data->page->mainRoute->published;

		parent::handleSave($form);
	}
}
