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

use Venne;
use Nette\Object;
use Venne\Forms\IControlExtension;
use Venne\Forms\Form;
use DoctrineModule\Forms\Controls\ManyToMany;
use DoctrineModule\Forms\Controls\ManyToOne;
use DoctrineModule\Forms\Mappers\EntityMapper;

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
		return $form[$name] = new \CmsModule\Content\Forms\Controls\FileEntityControl($name, $label);
	}
}
