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
class LanguageFormFactory extends FormFactory
{

	/** @var EntityMapper */
	protected $mapper;


	/**
	 * @param EntityMapper $mapper
	 * @param BaseRepository $repository
	 */
	public function __construct(EntityMapper $mapper)
	{
		$this->mapper = $mapper;
	}


	protected function getMapper()
	{
		return $this->mapper;
	}


	protected function getControlExtensions()
	{
		return array(
			new \FormsModule\ControlExtensions\ControlExtension(),
		);
	}


	/**
	 * @param Form $form
	 */
	public function configure(Form $form)
	{
		$form->addTextWithSelect("name", "Name")->setItems(array("English", "Deutsch", "Čeština"), false)->setOption("description", "(enhlish, deutsch,...)")->addRule($form::FILLED, "Please set name");
		$form->addTextWithSelect("short", "Short")->setItems(array("en", "de", "cs"), false)->setOption("description", "(en, de,...)")->addRule($form::FILLED, "Please set short");
		$form->addTextWithSelect("alias", "Alias")->setItems(array("en", "de", "cs", "www"), false)->setOption("description", "(www, en, de,...)")->addRule($form::FILLED, "Please set alias");

		$form->addSubmit('_submit', 'Save');
	}
}
