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

use DoctrineModule\Entities\IEntity;
use Nette\DI\Container;
use Nette\InvalidArgumentException;
use Nette\Object;

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

	/** @var ISocialLogin[] */
	protected $socialLogins = array();


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
	 * @throws InvalidArgumentException
	 */
	public function addUserType($name, $entity, $formFactoryName)
	{
		$entity = $this->normalizeEntityName($entity);

		if (isset($this->typesByName[$name])) {
			throw new InvalidArgumentException("User type name '{$name}' is already installed.");
		}

		if (isset($this->typesByEntity[$entity])) {
			throw new InvalidArgumentException("User type entity '{$entity}' is already installed.");
		}

		$this->types[$entity] = $name;
		$this->typesByName[$name] = $formFactoryName;
		$this->typesByEntity[$entity] = $formFactoryName;
	}


	/**
	 * @param $name
	 * @param ISocialLogin $socialLogin
	 * @throws InvalidArgumentException
	 */
	public function addSocialLogin($name, $socialLoginFactoryName)
	{
		if (isset($this->socialLogins[$name])) {
			throw new InvalidArgumentException("Social login name '{$name}' is already installed.");
		}

		$this->socialLogins[$name] = $socialLoginFactoryName;
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
	 * @throws InvalidArgumentException
	 */
	public function getFormFactoryByEntity($entity)
	{
		$entity = $this->normalizeEntityName($entity);

		if (!isset($this->typesByEntity[$entity])) {
			throw new InvalidArgumentException("Form factory for entity '{$entity}' has not been registered.");
		}

		$name = $this->typesByEntity[$entity];

		return $this->content->getService($name);
	}


	/**
	 * @param $name
	 * @return mixed
	 * @throws InvalidArgumentException
	 */
	public function getFormFactoryByName($name)
	{
		if (!isset($this->typesByName[$name])) {
			throw new InvalidArgumentException("Form factory for name '{$name}' has not been registered.");
		}

		$name = $this->typesByName[$name];

		return $this->content->getService($name);
	}


	/**
	 * @param $name
	 * @return ISocialLogin
	 * @throws InvalidArgumentException
	 */
	public function getSocialLoginByName($name)
	{
		if (!isset($this->socialLogins[$name])) {
			throw new InvalidArgumentException("Social login name '{$name}' has not been registered.");
		}

		return $this->content->getService($this->socialLogins[$name]);
	}


	/**
	 * @return array|ISocialLogin[]
	 */
	public function getSocialLogins()
	{
		return array_keys($this->socialLogins);
	}


	/**************************************** Protected ****************************************************/

	protected function normalizeEntityName($entity)
	{
		if ($entity instanceof IEntity) {
			$entity = get_class($entity);
		}

		return substr($entity, 0, 1) === '/' ? substr($entity, 1) : $entity;
	}
}
