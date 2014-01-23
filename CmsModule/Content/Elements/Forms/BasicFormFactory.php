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
		$form->addGroup();

		$langMode = $form->addSelect('langMode', 'Language mode', ElementEntity::getLangModes());
		$langMode
			->addCondition($form::EQUAL, ElementEntity::LANGMODE_SPLIT)->toggle('form-group-language');

		$form->setCurrentGroup($form->addGroup()->setOption('id', 'form-group-language'));
		$form->addManyToOne('language', 'Language')
			->addConditionOn($langMode, $form::EQUAL, ElementEntity::LANGMODE_SPLIT)->addRule($form::FILLED);

		$form->addGroup();
		$mode = $form->addSelect('mode', 'Share data with', ElementEntity::getModes());
		$mode
			->addCondition($form::IS_IN, array(1))->toggle('form-group-layout')
			->endCondition()
			->addCondition($form::IS_IN, array(2, 4))->toggle('form-group-page')
			->endCondition()
			->addCondition($form::EQUAL, 4)->toggle('form-group-route');

		$form->addGroup()->setOption('id', 'form-group-layout');
		$form->addManyToOne('layout', 'Layout')
			->addConditionOn($mode, $form::IS_IN, array(1))->addRule($form::FILLED);

		$form->addGroup()->setOption('id', 'form-group-page');
		$page = $form->addManyToOne('page', 'Page');
		$page
			->addConditionOn($mode, $form::IS_IN, array(2, 4))->addRule($form::FILLED);

		$form->addGroup()->setOption('id', 'form-group-route');
		$form->addManyToOne('route', 'Route')
			->setDependOn($page, 'page')
			->addConditionOn($mode, $form::EQUAL, 4)->addRule($form::FILLED);


		$form->setCurrentGroup();
		$form->addSaveButton('Save');
	}
}
