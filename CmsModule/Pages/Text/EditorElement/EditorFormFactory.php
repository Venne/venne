<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace CmsModule\Pages\Text\EditorElement;

use DoctrineModule\Forms\FormFactory;
use Venne\Forms\Form;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class EditorFormFactory extends FormFactory
{

	protected function getControlExtensions()
	{
		return array_merge(parent::getControlExtensions(), array(
			new \CmsModule\Content\ControlExtension(),
		));
	}


	/**
	 * @param Form $form
	 */
	public function configure(Form $form)
	{
		$form->addContentEditor('text', 'Text', 20, 5)->getControlPrototype()->attrs['class'][] = 'input-block-level';
		$form->addSaveButton('Save');
	}
}
