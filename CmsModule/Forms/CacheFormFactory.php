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

use Venne\Forms\Form;
use Venne\Forms\FormFactory;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class CacheFormFactory extends FormFactory
{

	/**
	 * @param Form $form
	 */
	protected function configure(Form $form)
	{
		$form->addGroup('Options');
		$form->addRadioList('section', 'Section', array('all' => 'All', 'namespace' => 'Namespace', 'sessions' => 'Sessions'))
			->setDefaultValue('all')
			->addCondition($form::EQUAL, 'namespace')->toggle('namespace');

		$form->addGroup('Namespace')->setOption('id', 'namespace');
		$form->addText('namespace');

		$form->addGroup();
		$form->addSaveButton('Clear');
	}
}
