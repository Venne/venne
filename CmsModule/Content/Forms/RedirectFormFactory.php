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

use DoctrineModule\Forms\FormFactory;
use Venne\Forms\Form;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class RedirectFormFactory extends FormFactory
{

	/**
	 * @param Form $form
	 */
	public function configure(Form $form)
	{
		$form->addGroup("Redirection");
		$form->addRadioList('type', 'Type', array('page' => 'Page', 'url' => 'URL'));
		$form['type']->addCondition($form::EQUAL, 'page')->toggle('page');
		$form['type']->addCondition($form::EQUAL, 'url')->toggle('url');
		$form['type']->getControlPrototype()->onClick = 'if( $(this).val() == "page" ) { $("#url").find("input").val("") } else ( $("#page").find("select").val("") );';

		$form->addGroup()->setOption('id', 'url');
		$form->addText("redirectUrl", "URL");

		$form->addGroup()->setOption('id', 'page');
		$form->addManyToOne('page', 'Page');

		$form->setCurrentGroup();
		$form->addSaveButton('Save');
	}


	public function handleLoad($form)
	{
		if ($form->data->page) {
			$form['type']->value = 'page';
		} else {
			$form['type']->value = 'url';
		}
	}
}
