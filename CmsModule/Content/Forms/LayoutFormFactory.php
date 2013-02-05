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
use DoctrineModule\Forms\Mappers\EntityMapper;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class LayoutFormFactory extends FormFactory
{

	/** @var Venne\Module\TemplateManager */
	protected $templateManager;

	/** @var array */
	protected $modules;


	public function __construct(EntityMapper $mapper, Venne\Module\TemplateManager $templateManager, $modules)
	{
		parent::__construct($mapper);

		$this->templateManager = $templateManager;
		$this->modules = & $modules;
	}


	/**
	 * @param Form $form
	 */
	protected function configure(Form $form)
	{
		$form->addText('name', 'Name');
		$form->addSelect('file', 'File')->setItems($this->templateManager->getLayouts());

		$form->addSaveButton('Save');
	}

}
