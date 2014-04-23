<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Files;

use Venne\Forms\IFormFactory;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class DirFormFactory implements IFormFactory
{

	/** @var IFormFactory */
	private $formFactory;


	public function __construct(IFormFactory $formFactory)
	{
		$this->formFactory = $formFactory;
	}


	public function create()
	{
		$form = $this->formFactory->create();

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

		return $form;
	}


//	public function handleSave(Form $form)
//	{
//		if ($form['recursively']->value) {
//			$form->data->setPermissionRecursively();
//		}
//
//		parent::handleSave($form);
//	}

}
