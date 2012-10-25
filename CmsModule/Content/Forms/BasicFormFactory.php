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

use Venne;
use Venne\Forms\Form;
use DoctrineModule\Forms\FormFactory;
use DoctrineModule\Forms\Mappers\EntityMapper;
use DoctrineModule\Repositories\BaseRepository;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class BasicFormFactory extends FormFactory
{

	protected function getControlExtensions()
	{
		return array(
			new \DoctrineModule\Forms\ControlExtensions\DoctrineExtension(),
			new \FormsModule\ControlExtensions\ControlExtension(),
		);
	}


	/**
	 * @param Form $form
	 */
	public function configure(Form $form)
	{
		$infoGroup = $form->addGroup('Informations');

		$form->addText('name', 'Name')
			->getControlPrototype()->attrs['onClick'] = "$(this).stringToSlug({setEvents: 'keyup keydown blur', getPut: '.localUrl', space: '-' }); this.onclick=null;";
		$form['name']->addRule($form::FILLED);

		// route
		$mainRoute = $form->addOne('mainRoute');
		$mainRoute->setCurrentGroup($infoGroup);
		$mainRoute->addText('localUrl', 'URL')
			->addRule($form::REGEXP, "URL can not contain '/'", "/^[a-zA-z0-9._-]*$/")
			->addRule($form::FILLED);

		$mainRoute['localUrl']->getControlPrototype()->class[] = 'localUrl';

		// parent
		if (!$form->data->translationFor) {
			if ($form->data->parent) {
				$form->addManyToOne("parent", "Parent content", NULL, NULL, array("translationFor" => NULL))->setPrompt(FALSE);
			}
		}

		// date
		$form->addDateTime("expired", "Expired");

		// Navigation
		$form->addGroup('Navigation');
		$form->addCheckbox('navigationShow', 'Show navigation')->addCondition($form::EQUAL, true)->toggle('form-navigation-own');
		$form->addGroup()->setOption('id', 'form-navigation-own');
		$form->addCheckbox('navigationOwn', 'Use own title')->addCondition($form::EQUAL, true)->toggle('form-navigation-title');
		$form->addGroup()->setOption('id', 'form-navigation-title');
		$form->addText('navigationTitleRaw', 'Navigation title');

		// languages
		/** @var $repository \DoctrineModule\Repositories\BaseRepository */
		$repository = $form->mapper->entityManager->getRepository('CmsModule\Content\Entities\LanguageEntity');
		if ($repository->createQueryBuilder('a')->select('COUNT(a)')->getQuery()->getSingleScalarResult() > 1) {
			$form->addGroup("Languages");
			$form->addManyToMany("languages", "Content is in")->addRule($form::FILLED, 'Page must contain some language');
		}

		$form->addGroup();
		$form->addSaveButton('Save');
	}


	public function handleSave(Form $form)
	{
		if ($form['navigationOwn']->value) {
			$form->data->navigationTitleRaw = $form['navigationTitleRaw']->value;
		} else {
			$form->data->navigationTitleRaw = NULL;
		}

		parent::handleSave($form);
	}


	public function handleLoad(Form $form)
	{
		if ($form->data->navigationTitleRaw !== null) {
			$form['navigationOwn']->value = true;
		}
	}


	public function handleCatchError(Form $form, $e)
	{
		if ($e instanceof \Nette\InvalidArgumentException) {
			$form->addError($e->getMessage());
			return true;
		}
	}
}
