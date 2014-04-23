<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\System\UI;

use Nette\Application\UI\Form;
use Nette\Object;
use Venne\Config\ConfigMapper;
use Venne\Forms\IFormFactory;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class ConfigFormFactory extends Object implements IFormFactory
{

	/** @var IFormFactory */
	private $formFactory;

	/** @var ConfigMapper */
	private $configMapper;


	/**
	 * @param $file
	 * @param $section
	 */
	public function __construct($file, $section)
	{
		$this->configMapper = new ConfigMapper($file, $section);
	}


	/**
	 * @param IFormFactory $formFactory
	 */
	public function setFormFactory(IFormFactory $formFactory)
	{
		$this->formFactory = $formFactory;
	}


	/**
	 * @return Form
	 */
	public function create()
	{
		$form = $this->formFactory ? $this->formFactory->create() : new Form;
		$this->configMapper->setForm($form);
		return $form;
	}

}
