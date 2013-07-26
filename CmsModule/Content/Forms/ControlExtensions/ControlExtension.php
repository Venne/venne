<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace CmsModule\Content\Forms\ControlExtensions;

use CmsModule\Content\Forms\Controls\TagsControl;
use DoctrineModule\Forms\Mappers\EntityMapper;
use Nette\Object;
use Venne\Forms\Form;
use Venne\Forms\IControlExtension;

/**
 * @author     Josef Kříž
 */
class ControlExtension extends Object implements IControlExtension
{

	/**
	 * @param Form $form
	 */
	public function check($form)
	{
		if (!$form->getMapper() instanceof EntityMapper) {
			throw new \Nette\InvalidArgumentException("Form mapper must be instanceof 'EntityMapper'. '" . get_class($form->getMapper()) . "' is given.");
		}

		if (!$form->getData() instanceof \DoctrineModule\Entities\IEntity) {
			throw new \Nette\InvalidArgumentException("Form data must be instanceof 'IEntity'. '" . get_class($form->getData()) . "' is given.");
		}
	}


	/**
	 * @return array
	 */
	public function getControls(Form $form)
	{
		$this->check($form);

		return array(
			'fileEntityInput',
			'contentTags',
		);
	}


	/**
	 * Adds upload input for FileEntity.
	 *
	 * @param $form
	 * @param $name
	 * @param null $label
	 * @return \DoctrineModule\Forms\Containers\EntityContainer
	 */
	public function addFileEntityInput($form, $name, $label = NULL)
	{
		return $form[$name] = new \CmsModule\Content\Forms\Controls\FileEntityControl($label);
	}


	/**
	 * Add tags input to the form.
	 *
	 * @param type $name
	 * @param type $label
	 */
	public function addContentTags($form, $name, $label = NULL)
	{
		$control = $form[$name] = new TagsControl($label);
		return $control;
	}
}
