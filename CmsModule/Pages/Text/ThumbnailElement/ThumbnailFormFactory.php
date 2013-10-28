<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace CmsModule\Pages\Text\ThumbnailElement;

use CmsModule\Pages\Text\ImageElement\AbstractImageFormFactory;
use Venne\Forms\Form;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class ThumbnailFormFactory extends AbstractImageFormFactory
{

	/**
	 * @param Form $form
	 */
	public function configure(Form $form)
	{
		$form->addGroup();
		$form->addFileEntityInput('image', 'Image');
		$form->addText('alt', 'Alt');
		$form->addTextArea('description', 'Description');

		if (!$form->data->hideWidth || !$form->data->hideHeight || !$form->data->hideFormat) {
			$form->addGroup('Size');

			if (!$form->data->hideWidth) {
				$form->addText('width', 'Width')
					->addCondition($form::FILLED)->addRule($form::INTEGER);
			}

			if (!$form->data->hideHeight) {
				$form->addText('height', 'Height')
					->addCondition($form::FILLED)->addRule($form::INTEGER);
			}

			if (!$form->data->hideFormat) {
				$form->addSelect('format', 'Format', array(
					0 => 'Fit',
					1 => 'Shrink only',
					2 => 'Stretch',
					4 => 'Fill',
					8 => 'Exact'
				));
			}
		}

		if (!$form->data->hideType) {
			$form->addSelect('type', 'Type', array(
				'png' => 'PNG',
				'jpeg' => 'JPEG',
				'gif' => 'GIF',
			))->setPrompt('-- default --');
		}

		$form->addGroup();
		$form->addSaveButton('Save');
	}
}
