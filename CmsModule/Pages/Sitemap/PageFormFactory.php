<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace CmsModule\Pages\Sitemap;

use DoctrineModule\Forms\FormFactory;
use Venne\Forms\Form;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class PageFormFactory extends FormFactory
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
		$form->addGroup('Options');
		$form->addManyToOne('rootPage', 'Root page')
			->setPrompt(NULL);
		$form->addText('maxDepth', 'Maximal depth')
			->addRule($form::INTEGER);
		$form->addText('maxWidth', 'Maximal width')
			->addRule($form::INTEGER);

		$form->setCurrentGroup();
		$form->addSaveButton('Save');
	}
}
