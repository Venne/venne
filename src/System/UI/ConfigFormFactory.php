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
use Venne\Config\ConfigMapper;
use Venne\Forms\IFormFactory;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class ConfigFormFactory extends \Nette\Object implements \Venne\Forms\IFormFactory
{

	/** @var \Venne\Forms\IFormFactory */
	private $formFactory;

	/** @var \Venne\Config\ConfigMapper */
	private $configMapper;

	/**
	 * @param string $file
	 * @param string $section
	 */
	public function __construct($file, $section)
	{
		$this->configMapper = new ConfigMapper($file, $section);
	}

	/**
	 * @param \Venne\Forms\IFormFactory $formFactory
	 */
	public function setFormFactory(IFormFactory $formFactory)
	{
		$this->formFactory = $formFactory;
	}

	/**
	 * @return \Nette\Application\UI\Form
	 */
	public function create()
	{
		$form = $this->formFactory ? $this->formFactory->create() : new Form;
		$this->configMapper->setForm($form);

		return $form;
	}

}
