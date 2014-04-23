<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Security;

use Nette\Object;
use Venne\Forms\IFormFactory;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class UserType extends Object
{

	/** @var string */
	private $name;

	/** @var string */
	private $entityName;

	/** @var IFormFactory */
	private $formFactory;

	/** @var IFormFactory */
	private $frontFormFactory;

	/** @var IFormFactory */
	private $registrationFormFactory;


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
	 * @param IFormFactory $formFactory
	 */
	public function setFormFactory(IFormFactory $formFactory)
	{
		$this->formFactory = $formFactory;
	}


	/**
	 * @return IFormFactory
	 */
	public function getFormFactory()
	{
		return $this->formFactory;
	}


	/**
	 * @param IFormFactory $formFactory
	 */
	public function setFrontFormFactory(IFormFactory $formFactory)
	{
		$this->frontFormFactory = $formFactory;
	}


	/**
	 * @return IFormFactory
	 */
	public function getFrontFormFactory()
	{
		return $this->frontFormFactory;
	}


	/**
	 * @param IFormFactory $formFactory
	 */
	public function setRegistrationFormFactory(IFormFactory $formFactory)
	{
		$this->registrationFormFactory = $formFactory;
	}


	/**
	 * @return IFormFactory
	 */
	public function getRegistrationFormFactory()
	{
		return $this->registrationFormFactory;
	}
}
