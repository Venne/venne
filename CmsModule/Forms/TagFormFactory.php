<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace CmsModule\Forms;

use CmsModule\Content\Repositories\LanguageRepository;
use CmsModule\Pages\Tags\TagEntity;
use DoctrineModule\Forms\FormFactory;
use Venne\Forms\Form;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class TagFormFactory extends FormFactory
{

	/**
	 * @param Form $form
	 */
	public function configure(Form $form)
	{
		$form->addGroup();
		$form->addText('name', 'Name')
			->addRule($form::FILLED, 'Please set name');
		$form->addTextArea('notation', 'Notation', NULL, 3);

		$form->addGroup('Langauge variants');

		$languages = $form->addContainer('languages');
		foreach ($this->getLanguageRepository()->findBy(array(), array('id' => 'ASC'), NULL, 1) as $language) {
			$c = $form->addCheckbox('lang_' . $language->id, (string)$language);

			$lang = $languages->addContainer($language->id);
			$lang->setCurrentGroup($form->addGroup($language->name)->setOption('id', 'group-' . $language->id));

			$lang->addText('name', 'Name')
				->addConditionOn($c, $form::EQUAL, TRUE)
				->addRule($form::FILLED);
			$lang->addTextArea('notation', 'Notation', NULL, 3);

			$c->addCondition($form::EQUAL, TRUE)->toggle('group-' . $language->id);
		}

		$form->setCurrentGroup();
		$form->addSaveButton('Save');
	}


	public function handleSave(Form $form)
	{
		/** @var TagEntity $entity */
		$entity = $form->data;

		$a = $entity->getLocale();
		foreach ($this->getLanguageRepository()->findBy(array(), array('id' => 'ASC'), NULL, 1) as $language) {
			$entity->setLocale($language);
			if ($form['lang_' . $language->id]->value) {
				$entity->name = $form['languages'][$language->id]['name']->value;
				$entity->route->notation = $form['languages'][$language->id]['notation']->value;
			} else {
				$entity->name = NULL;
				$entity->route->notation = NULL;
			}
		}
		$entity->setLocale($a);
		$entity->name = $form['name']->value;
		$entity->route->notation = $form['notation']->value;

		parent::handleSave($form);
	}


	public function handleLoad(Form $form)
	{
		/** @var TagEntity $entity */
		$entity = $form->data;

		$a = $entity->getLocale();
		$name = $entity->getName();
		$notation = $entity->route->getNotation();
		foreach ($this->getLanguageRepository()->findBy(array(), array('id' => 'ASC'), NULL, 1) as $language) {
			$entity->setLocale($language);

			if ($name != $entity->route->name || $notation != $entity->route->notation) {
				$form['languages'][$language->id]['name']->value = $entity->route->name;
				$form['languages'][$language->id]['notation']->value = $entity->route->notation;
				$form['lang_' . $language->id]->value = TRUE;
			}
		}
		$entity->setLocale($a);
		$form['name']->value = $entity->name;
		$form['notation']->value = $entity->route->notation;
	}


	/**
	 * @return LanguageRepository
	 */
	private function getLanguageRepository()
	{
		return $this->getMapper()->getEntityManager()->getRepository('CmsModule\Content\Entities\LanguageEntity');
	}
}
