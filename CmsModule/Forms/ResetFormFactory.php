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
class ResetFormFactory extends FormFactory
{

	/**
	 * @param Form $form
	 */
	public function configure(Form $form)
	{
		$form->addText('email', 'Email')
			->addRule($form::FILLED)
			->addRule($form::EMAIL);

		$form->addSaveButton('Reset password')
			->getControlPrototype()->class[] = 'btn-primary';
		$form->addSubmit('cancel', 'Cancel')
			->setValidationScope(FALSE);
	}

}
