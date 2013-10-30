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

use CmsModule\Content\PermissionDeniedException;
use CmsModule\Pages\Users\UserEntity;
use Doctrine\DBAL\DBALException;
use DoctrineModule\Forms\FormFactory;
use Nette\InvalidArgumentException;
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
		$form->addGroup();
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

		$form->addGroup('Permissions');
		$form->addManyToOne('author', 'Owner');
		$form->addManyToMany('write', 'Write');
		$form->addCheckbox('protected', 'Protected')
			->addCondition($form::EQUAL, TRUE)
			->toggle('form-permissions');

		$form->addGroup()->setOption('id', 'form-permissions');
		$form->addManyToMany('read', 'Read');

		$form->addGroup();
		$form->addSaveButton('Save');
	}


	public function handleCatchError(Form $form, $e)
	{
		if ($e instanceof PermissionDeniedException) {
			$form->addError('You have not writable permissions.');
			return TRUE;
		}
	}


	public function handleSuccess(Form $form)
	{
		if (isset($form->presenter['panel'])) {
			$form->presenter['panel']->invalidateControl('content');
		}
	}
}
