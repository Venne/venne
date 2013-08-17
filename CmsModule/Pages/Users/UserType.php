<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace CmsModule\Pages\Users;

use Nette\Object;
use Venne\Forms\FormFactory;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class UserType extends Object
{

	/** @var string */
	protected $name;

	/** @var string */
	protected $entityName;

	/** @var FormFactory */
	protected $formFactory;


	/**
	 * @param $name
	 * @param $entityName
	 */
	public function __construct($name, $entityName)
	{
		$this->name = $name;
		$this->entityName = $entityName;
	}


	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}


	/**
	 * @return string
	 */
	public function getEntityName()
	{
		return $this->entityName;
	}


	/**
	 * @param FormFactory $formFactory
	 */
	public function setFormFactory(FormFactory $formFactory)
	{
		$this->formFactory = $formFactory;
	}


	/**
	 * @return FormFactory
	 */
	public function getFormFactory()
	{
		return $this->formFactory;
	}
}
