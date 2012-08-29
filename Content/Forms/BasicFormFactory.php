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
use DoctrineModule\ORM\BaseRepository;

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
		);
	}


	/**
	 * @param Form $form
	 */
	public function configure(Form $form)
	{
		$infoGroup = $form->addGroup('Informations');

		$form->addText('name', 'Name');

		//$form->addCheckbox("mainPage", "Main page");
		if (!$form->data->translationFor) {
			$form->addManyToOne("parent", "Parent content", NULL, NULL, array("translationFor" => NULL));
		}
		//$form->addText("localUrl", "URL")->setOption("description", "(example: 'contact')")->addRule($form::REGEXP, "URL can not contain '/'", "/^[a-zA-z0-9._-]*$/");
		$mainRoute = $form->addOne('mainRoute');
		$mainRoute->setCurrentGroup($infoGroup);
		$mainRoute->addText('localUrl', 'URL');

		$form->addGroup("Dates");
		//$form->addDateTime("created", "Created")->setDefaultValue(new \Nette\DateTime);
		//$form->addDateTime("updated", "Updated")->setDefaultValue(new \Nette\DateTime);
		//$form->addDateTime("expired", "Expired");


		// URL can be empty only on main page
		if (!$form->data->translationFor) {
			$form['mainRoute']["localUrl"]->addConditionOn($form["parent"], ~$form::EQUAL, false)->addRule($form::FILLED, "URL can be empty only on main page");
		} else if ($form->data->translationFor && $form->data->translationFor->parent) {
			$form['mainRoute']["localUrl"]->addRule($form::FILLED, "URL can be empty only on main page");
		}

		// languages
		/** @var $repository \DoctrineModule\ORM\BaseRepository */
		$repository = $form->mapper->entityManager->getRepository('CmsModule\Content\Entities\LanguageEntity');
		if ($repository->createQueryBuilder('a')->select('COUNT(a)')->getQuery()->getSingleScalarResult() > 1) {
			$form->addGroup("Languages");
			$form->addManyToMany("languages", "Content is in");
		}

		$form->setCurrentGroup();

		$form->addSubmit('_submit', 'Save');
	}


	public function handleSave(Form $form)
	{
		try {
			$this->repository->save($form->data);
		} catch (\Nette\InvalidArgumentException $e) {
			$form->addError("URL is not unique");
		}
	}
}
