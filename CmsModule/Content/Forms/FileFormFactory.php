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
class FileFormFactory extends FormFactory
{

	/**
	 * @param Form $form
	 */
	protected function configure(Form $form)
	{
		if ($form->data->id) {
			$form->addText('name', 'Name')
				->addCondition($form::FILLED);
		} else {
			$form->addUpload('file', 'File')
				->addCondition($form::FILLED);
		}

		$form->addManyToOne('parent', 'Parent')
			->setCriteria(array('invisible' => FALSE))
			->setOrderBy(array('path' => 'ASC'));

		$form->addSaveButton('Save');
	}


	public function handleSuccess(Form $form)
	{
		if (isset($form->presenter['panel'])) {
			$form->presenter['panel']->invalidateControl('content');
		}
	}
}
