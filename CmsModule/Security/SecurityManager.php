<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace CmsModule\Security;

use Venne;
use Nette\Object;
use Nette\DI\Container;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class SecurityManager extends Object
{


	/** @var Container */
	protected $content;

	/** @var array */
	protected $types = array();

	/** @var array */
	protected $typesByEntity = array();

	/** @var array */
	protected $typesByName = array();


	/**
	 * @param Container $content
	 */
	public function __construct(Container $content)
	{
		$this->content = $content;
	}


	/**
	 * @param $name
	 * @param $entity
	 * @param $formFactoryName
	 * @throws \Nette\InvalidArgumentException
	 */
	public function addUserType($name, $entity, $formFactoryName)
	{
		$entity = $this->normalizeEntityName($entity);

		if (isset($this->typesByName[$name])) {
			throw new \Nette\InvalidArgumentException("User type name '{$name}' is already installed.");
		}

		if (isset($this->typesByEntity[$entity])) {
			throw new \Nette\InvalidArgumentException("User type entity '{$entity}' is already installed.");
		}

		$this->types[$entity] = $name;
		$this->typesByName[$name] = $formFactoryName;
		$this->typesByEntity[$entity] = $formFactoryName;
	}


	/**
	 * @return mixed
	 */
	public function getTypes()
	{
		return $this->types;
	}


	/**
	 * @param $entity
	 * @return mixed
	 * @throws \Nette\InvalidArgumentException
	 */
	public function getFormFactoryByEntity($entity)
	{
		$entity = $this->normalizeEntityName($entity);

		if (!isset($this->typesByEntity[$entity])) {
			throw new \Nette\InvalidArgumentException("Form factory for entity '{$entity}' has not been registered.");
		}

		$name = $this->typesByEntity[$entity];

		return $this->content->getService($name);
	}


	/**
	 * @param $name
	 * @return mixed
	 * @throws \Nette\InvalidArgumentException
	 */
	public function getFormFactoryByName($name)
	{
		if (!isset($this->typesByName[$name])) {
			throw new \Nette\InvalidArgumentException("Form factory for name '{$name}' has not been registered.");
		}

		$name = $this->typesByName[$name];

		return $this->content->getService($name);
	}


	protected function normalizeEntityName($entity)
	{
		if ($entity instanceof \DoctrineModule\Entities\IEntity) {
			$entity = get_class($entity);
		}

		return substr($entity, 0, 1) === '/' ? substr($entity, 1) : $entity;
	}
}
