<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace CmsModule\Administration\Forms;

use DoctrineModule\Forms\FormFactory;
use FormsModule\ControlExtensions\ControlExtension;
use Kdyby\Replicator\Container;
use Venne\Forms\Form;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class MailFormFactory extends FormFactory
{

	protected function getControlExtensions()
	{
		return array_merge(parent::getControlExtensions(), array(
			new ControlExtension,
		));
	}


	/**
	 * @param Form $form
	 */
	public function configure(Form $form)
	{
		$group = $form->addGroup();
		$form->addText('subject', 'Subject');
		$sender = $form->addOne('sender');
		$sender->setCurrentGroup($group);

		$sender->addManyToOne('user', 'Sender');

		$recipients = $form->addMany('recipients', function($container) use ($group) {
			$container->setCurrentGroup($group);
			$container->addManyToOne('user', 'Recipient');

			$container->addSubmit('remove', 'Smazat')
				->addRemoveOnClick();
		});
		$recipients->setCurrentGroup($group);
		$recipients->addSubmit('add', 'Add')
			->addCreateOnClick();

		$form->addGroup('Text');
		$form->addTextArea('text');

		$form->setCurrentGroup();
		$form->addSaveButton('Save');
	}

}
