<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace CmsModule\Content;

use Venne;
use Nette\Object;
use Venne\Forms\IControlExtension;
use Venne\Forms\Form;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class ControlExtension extends Object implements IControlExtension
{

	/**
	 * @return array
	 */
	public function getControls(Form $form)
	{
		return array(
			'contentEditor',
		);
	}


	/**
	 * Adds multi-line text input control to the form.
	 * @param $form
	 * @param  string  control name
	 * @param  string  label
	 * @param  int  width of the control
	 * @param  int  height of the control in text lines
	 * @return \Nette\Forms\Controls\TextArea
	 */
	public function addContentEditor($form, $name, $label = NULL, $cols = 40, $rows = 10)
	{
		$evm = $form->getMapper()->entityManager->getEventManager();
		return $form[$name] = new \CmsModule\Content\Forms\Controls\ContentEditor($evm, $label, $cols, $rows);
	}
}
