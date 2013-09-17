<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace CmsModule\Content\Elements\Forms;

use CmsModule\Content\Elements\ElementEntity;
use CmsModule\Content\Repositories\PageRepository;
use DoctrineModule\Forms\FormFactory;
use Venne\Forms\Form;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class BasicFormFactory extends FormFactory
{

	/**
	 * @param Form $form
	 */
	public function configure(Form $form)
	{
		$element = $form->addOne('element');

		$element->setCurrentGroup($form->addGroup());
		$element->addSelect('langMode', 'Language mode', ElementEntity::getLangModes())
			->addCondition($form::EQUAL, ElementEntity::LANGMODE_SPLIT)->toggle('form-group-language');

		$element->setCurrentGroup($form->addGroup()->setOption('id', 'form-group-language'));
		$element->addManyToOne('language', 'Language');

		$element->setCurrentGroup($form->addGroup());
		$mode = $element->addSelect('mode', 'Share data with', ElementEntity::getModes());
		$mode
			->addCondition($form::IS_IN, array(1, 2, 4))->toggle('form-group-layout')
			->endCondition()
			->addCondition($form::IS_IN, array(2, 4))->toggle('form-group-page')
			->endCondition()
			->addCondition($form::EQUAL, 4)->toggle('form-group-route');

		$element->setCurrentGroup($form->addGroup()->setOption('id', 'form-group-layout'));
		$element->addManyToOne('layout', 'Layout');

		$element->setCurrentGroup($form->addGroup()->setOption('id', 'form-group-page'));
		$page = $element->addManyToOne('page', 'Page');
		$page
			->addConditionOn($mode, $form::IS_IN, array(1, 2))->addRule($form::FILLED);

		$element->setCurrentGroup($form->addGroup()->setOption('id', 'form-group-route'));
		$element->addManyToOne('route', 'Route')
			->setDependOn($page, 'page')
			->addConditionOn($mode, $form::EQUAL, 2)->addRule($form::FILLED);


		$form->addGroup();
		$form->addSaveButton('Save');
	}
}
