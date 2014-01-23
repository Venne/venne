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

use DoctrineModule\Forms\FormFactory;
use Venne\Forms\Form;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class DirFormFactory extends FormFactory
{

	/**
	 * @param Form $form
	 */
	protected function configure(Form $form)
	{
		$form->addGroup();
		$form->addText('name', 'Name');

		if ($form->data->id) {
			$form->addManyToOne('parent', 'Parent')
				->setCriteria(array('invisible' => FALSE))
				->setOrderBy(array('path' => 'ASC'));
		}

		$form->addGroup('Permissions');
		$form->addManyToOne('author', 'Owner');
		$form->addManyToMany('write', 'Write');
		$form->addManyToMany('read', 'Read');
		$form->addCheckbox('protected', 'Protected');
		$form->addCheckbox('recursively', 'Change recursively');

		$form->setCurrentGroup();
		$form->addSaveButton('Save');
	}


	public function handleSave(Form $form)
	{
		if ($form['recursively']->value) {
			$form->data->setPermissionRecursively();
		}

		parent::handleSave($form);
	}

}
