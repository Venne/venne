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

use Venne;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class BaseForm extends \Venne\Application\UI\Form
{


	public function startup()
	{
		parent::startup();

		$this->setCurrentGroup();
		$this->addSubmit('_submit', "Save changes")->getControlPrototype()->class[] = 'btn btn-primary';
		$this->addSubmit('_cancel', "Cancel")
			->setValidationScope(false)
			->getControlPrototype()->class[] = 'btn';
	}
}
