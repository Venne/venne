<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace CmsModule\Content\Elements\Forms;

use DoctrineModule\Forms\FormFactory;
use Venne\Forms\Form;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class ClearFormFactory extends FormFactory
{

	/**
	 * @param Form $form
	 */
	public function configure(Form $form)
	{
		$form->addSelect('use', 'Clear data', array(false => 'No', true => 'Yes'));
		$form->addSaveButton('Clear');
	}


	public function handleSave(Form $form)
	{
		if ($form['use']->value) {
			$this->mapper->getEntityManager()->getRepository(get_class($form->data))->delete($form->data);
		}
	}
}
