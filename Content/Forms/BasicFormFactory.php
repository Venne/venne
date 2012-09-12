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
use Venne\Forms\FormFactory;
use Venne\Forms\Form;
use DoctrineModule\Forms\Mappers\EntityMapper;
use DoctrineModule\Repositories\BaseRepository;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class BasicFormFactory extends FormFactory
{

	/** @var EntityMapper */
	protected $mapper;

	/** @var BaseRepository */
	protected $repository;


	/**
	 * @param EntityMapper $mapper
	 * @param BaseRepository $repository
	 */
	public function __construct(EntityMapper $mapper, BaseRepository $repository)
	{
		$this->mapper = $mapper;
		$this->repository = $repository;
	}


	protected function getMapper()
	{
		return $this->mapper;
	}


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

		if (!$form->data->translationFor) {
			$form->addManyToOne("parent", "Parent content", NULL, NULL, array("translationFor" => NULL));
		}

		// route
		$mainRoute = $form->addOne('mainRoute');
		$mainRoute->setCurrentGroup($infoGroup);
		$mainRoute->addText('localUrl', 'URL')
			->addRule($form::REGEXP, "URL can not contain '/'", "/^[a-zA-z0-9._-]*$/")
			->addConditionOn($form['parent'], $form::FILLED)
				->addRule($form::FILLED);
		$mainRoute['localUrl']->getControlPrototype()->class[] = 'localUrl';

		// date
		$form->addDateTime("expired", "Expired");

		// URL can be empty only on main page
		if (!$form->data->translationFor) {
			$form['mainRoute']["localUrl"]->addConditionOn($form["parent"], ~$form::EQUAL, false)->addRule($form::FILLED, "URL can be empty only on main page");
		} else if ($form->data->translationFor && $form->data->translationFor->parent) {
			$form['mainRoute']["localUrl"]->addRule($form::FILLED, "URL can be empty only on main page");
		}

		// languages
		/** @var $repository \DoctrineModule\Repositories\BaseRepository */
		$repository = $form->mapper->entityManager->getRepository('CmsModule\Content\Entities\LanguageEntity');
		if ($repository->createQueryBuilder('a')->select('COUNT(a)')->getQuery()->getSingleScalarResult() > 1) {
			$form->addGroup("Languages");
			$form->addManyToMany("languages", "Content is in")->addRule($form::FILLED, 'Page must contain some language');
		}

		$form->addSubmit('_submit', 'Save');
	}


	public function handleSave(Form $form)
	{
		try {
			$this->repository->save($form->data);
		} catch (\Nette\InvalidArgumentException $e) {
			$form->addError($e->getMessage());
		}
	}
}
