<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Security\DI;

use Nette\DI\Statement;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class UserType extends \Nette\Object
{

	/** @var string */
	private $name;

	/** @var string */
	private $entityName;

	/** @var \Nette\DI\Statement */
	private $formFactory;

	/** @var \Nette\DI\Statement */
	private $frontFormFactory;

	/** @var \Nette\DI\Statement */
	private $registrationFormFactory;

	/**
	 * @param string $name
	 * @param string $entityName
	 * @param \Nette\DI\Statement $formFactory
	 * @param \Nette\DI\Statement $frontFormFactory
	 * @param \Nette\DI\Statement $registrationFormFactory
	 */
	public function __construct($name, $entityName, Statement $formFactory, Statement $frontFormFactory, Statement $registrationFormFactory)
	{
		$this->name = $name;
		$this->entityName = $entityName;
		$this->formFactory = $formFactory;
		$this->frontFormFactory = $frontFormFactory;
		$this->registrationFormFactory = $registrationFormFactory;
	}

	/**
	 * @return mixed[]
	 */
	public function getArguments()
	{
		return array(
			$this->name,
			$this->entityName,
			$this->formFactory,
			$this->frontFormFactory,
			$this->registrationFormFactory,
		);
	}

}
