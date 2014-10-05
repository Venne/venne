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

use Venne\System\DoctrineFormService;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class UserType extends \Nette\Object
{

	/** @var string */
	private $name;

	/** @var string */
	private $entityName;

	/** @var \Venne\System\DoctrineFormService */
	private $formService;

	/** @var \Venne\System\DoctrineFormService */
	private $frontFormService;

	/** @var \Venne\System\DoctrineFormService */
	private $registrationFormService;

	/**
	 * @param string $name
	 * @param string $entityName
	 * @param \Venne\System\DoctrineFormService $formService
	 * @param \Venne\System\DoctrineFormService $frontFormService
	 * @param \Venne\System\DoctrineFormService $registrationFormService
	 */
	public function __construct($name, $entityName, DoctrineFormService $formService, DoctrineFormService $frontFormService, DoctrineFormService $registrationFormService)
	{
		$this->name = $name;
		$this->entityName = $entityName;
		$this->formService = $formService;
		$this->frontFormService = $frontFormService;
		$this->registrationFormService = $registrationFormService;
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
	 * @return \Venne\System\DoctrineFormService
	 */
	public function getFormService()
	{
		return $this->formService;
	}

	/**
	 * @return \Venne\System\DoctrineFormService
	 */
	public function getFrontFormService()
	{
		return $this->frontFormService;
	}

	/**
	 * @return \Venne\System\DoctrineFormService
	 */
	public function getRegistrationFormService()
	{
		return $this->registrationFormService;
	}

}
