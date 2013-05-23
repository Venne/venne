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
use Venne\Forms\Form;
use DoctrineModule\Forms\FormFactory;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class LanguageFormFactory extends FormFactory
{


	protected function getControlExtensions()
	{
		return array_merge(parent::getControlExtensions(), array(
			new \FormsModule\ControlExtensions\ControlExtension(),
		));
	}


	/**
	 * @param Form $form
	 */
	public function configure(Form $form)
	{
		$form->addTextWithSelect("name", "Name")
			->setItems(array("English", "Deutsch", "Čeština"), false)
			->setOption("description", "(enhlish, deutsch,...)")
			->addRule($form::FILLED, "Please set name");

		$form->addTextWithSelect("short", "Short")
			->setItems(array("en", "de", "cs"), false)
			->setOption("description", "(en, de,...)")
			->addRule($form::FILLED, "Please set short");

		$form->addTextWithSelect("alias", "Alias")
			->setItems(array("en", "de", "cs", "www"), false)
			->setOption("description", "(www, en, de,...)")
			->addRule($form::FILLED, "Please set alias");

		$form->addSaveButton('Save');
	}


	public function handleCatchError(Form $form, $e)
	{
		$m = explode("'", $e->getMessage());
		$form->addError("Duplicate entry '{$m[1]}'");
		return true;
	}


	public function handleSuccess(Form $form)
	{
		$form->getPresenter()->flashMessage('Language has been saved', 'success');
	}
}
