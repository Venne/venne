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

use Venne;
use Venne\Forms\Form;
use DoctrineModule\Forms\FormFactory;

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
		$form->addText('name', 'Name');
		$form->addManyToOne('parent', 'Parent');

		$form->addSaveButton('Save');
	}
}
