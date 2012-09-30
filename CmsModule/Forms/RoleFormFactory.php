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
class RoleFormFactory extends FormFactory
{


	/**
	 * @param Form $form
	 */
	public function configure(Form $form)
	{
		$form->addText("name", "Name");
		$form->addManyToOne("parent", "Parent")->setPrompt("root");

		$form->addSaveButton('Save');
	}


	public function handleCatchError(Form $form, \DoctrineModule\SqlException $e)
	{
		if ($e->getCode() == '23000') {
			$form->addError("Role is not unique");
			return true;
		}
	}


	public function handleSuccess(Form $form)
	{
		$form->getPresenter()->flashMessage('Role has been saved', 'success');
	}
}
