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

use Venne;
use Venne\Forms\FormFactory;
use Venne\Forms\Form;
use DoctrineModule\Forms\Mappers\EntityMapper;
use DoctrineModule\Repositories\BaseRepository;


/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class UserFormFactory extends FormFactory
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
	protected function configure(Form $form)
	{
		$group = $form->addGroup();
		$form->addCheckbox("enable", "Enable")->setDefaultValue(true);
		$form->addText("email", "E-mail")
			->addRule(Form::EMAIL, "Enter email");

		$form->addText("key", "Authentization key")->setOption("description", "If is set user cannot log in.");

		$form->addCheckbox("password_new", "Set password")->addCondition($form::EQUAL, true)->toggle('setPasswd');
		$form->addGroup()->setOption('id', 'setPasswd');
		$form->addPassword("_password", "Password")
			->setOption("description", "minimal length is 5 char")
			->addConditionOn($form['password_new'], Form::FILLED)
			->addRule(Form::FILLED, 'Enter password')
			->addRule(Form::MIN_LENGTH, 'Password is short', 5);
		$form->addPassword("password_confirm", "Confirm password")
			->addRule(Form::EQUAL, 'Invalid re password', $form['_password']);

		$form->addGroup("Next informations");
		$form->addManyToMany("roleEntities");

		$form->addSubmit('_submit', 'Save');
	}


	public function handleSave(Form $form)
	{
		if ($form["password_new"]->value) {
			$form->data->password = $form["_password"]->value;
		}

		try {
			$this->repository->save($form->data);
		} catch (\DoctrineModule\ORM\SqlException $e) {
			if ($e->getCode() == 23000) {
				$form->addError("User {$form->data->name} already exists");
				return;
			} else {
				throw $e;
			}
		}
	}
}
