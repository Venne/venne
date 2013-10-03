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

use DoctrineModule\Forms\FormFactory;
use DoctrineModule\SqlException;
use Venne\Forms\Form;

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


	public function handleSuccess(Form $form)
	{
		$form->getPresenter()->flashMessage($form->presenter->translator->translate('Role has been saved'), 'success');
	}
}
