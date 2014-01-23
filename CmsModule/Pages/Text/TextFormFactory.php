<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace CmsModule\Pages\Text;

use DoctrineModule\Forms\FormFactory;
use Venne\Forms\Form;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class TextFormFactory extends FormFactory
{

	protected function getControlExtensions()
	{
		return array_merge(parent::getControlExtensions(), array(
			new \CmsModule\Content\ControlExtension,
			new \FormsModule\ControlExtensions\ControlExtension,
		));
	}


	/**
	 * @param Form $form
	 */
	public function configure(Form $form)
	{
		$route = $form->addOne('page')->addOne('mainRoute');
		$route->setCurrentGroup($form->addGroup()->setOption('class', 'full'));
		$route->addContentEditor('text', NULL, NULL, 30)->getControlPrototype()->class[] = 'input-block-level';
		$route['text']->getControlPrototype()->data['cms-route'] = $form->data->page->mainRoute->id;
		$route['text']->getControlPrototype()->data['cms-page'] = $form->data->page->id;

		$form->setCurrentGroup();
		$form->addSaveButton('Save');
	}
}
