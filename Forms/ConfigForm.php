<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace CmsModule\Forms;

use Venne;
use Nette\Application\UI;
use Nette\Application\UI\Presenter;
use AssetsModule\Managers\AssetManager;

/**
 * @author	 Josef Kříž
 */
class ConfigForm extends \FormsModule\Form
{


	/** @var array of function(Form $form, $entity); Occurs when the form is submitted, valid and entity is saved */
	public $onSave = array();

	/** @var string key of application stored request */
	private $onSaveRestore;

	/** @var Mapping\EntityFormMapper */
	private $mapper;



	/**
	 * @param object $entity
	 * @param Mapping\EntityFormMapper $mapper
	 */
	public function __construct(AssetManager $assetManager, \FormsModule\Mapping\ConfigFormMapper $mapper)
	{
		$this->mapper = $mapper;
		$this->mapper->setContainer($this);
		parent::__construct($assetManager);
	}



	public function getRoot()
	{
		return $this->mapper->root;
	}



	public function setRoot($root)
	{
		$this->mapper->root = $root;
	}



	/**
	 * @return Mapping\EntityFormMapper
	 */
	public function getMapper()
	{
		return $this->mapper;
	}



	/**
	 * @param Nette\ComponentModel\Container $obj
	 */
	protected function attached($obj)
	{
		parent::attached($obj);

		if ($obj instanceof Presenter) {
			if (!$this->isSubmitted()) {
				$this->getMapper()->load();
			} else {
				$this->getMapper()->save();
			}
		}
	}



	/* -------------------------- */


	/**
	 * @param string $name
	 * @return Containers\Doctrine\EntityContainer
	 */
	public function addOneToOne($name)
	{
		$entity = $this->getMapper()->getAssocation($this->getEntity(), $name);
		return $this[$name] = new Containers\Doctrine\EntityContainer($entity);
	}



	/**
	 * @param string $name
	 * @return Containers\Doctrine\EntityContainer
	 */
	public function addManyToOneContainer($name)
	{
		$entity = $this->getMapper()->getAssocation($this->getEntity(), $name);
		return $this[$name] = new Containers\Doctrine\EntityContainer($entity);
	}



	/**
	 * @param string $name
	 * @return Containers\Doctrine\EntityContainer
	 */
	public function addManyToOne($name, $label = NULL, $items = NULL, $size = NULL, array $criteria = array(), array $orderBy = NULL, $limit = NULL, $offset = NULL)
	{
		$ref = $this->entity->getReflection()->getProperty($name);

		if ($ref->hasAnnotation("Form")) {
			$ref = $ref->getAnnotation("Form");
			$class = $ref["targetEntity"];
			if (substr($class, 0, 1) != "\\") {
				$class = "\\" . $this->entity->getReflection()->getNamespaceName() . "\\" . $class;
			}
		} else {
			$ref = $ref->getAnnotation("ManyToOne");
			$class = $ref["targetEntity"];
			if (substr($class, 0, 1) != "\\") {
				$class = "\\" . $this->entity->getReflection()->getNamespaceName() . "\\" . $class;
			}
		}

		$items = $this->entityManager->getRepository($class)->findBy($criteria, $orderBy, $limit, $offset);

		$this[$name] = new Controls\ManyToOne($label, $items, $size);
		$this[$name]->setPrompt("---------");
		return $this[$name];
	}



	/**
	 * @param string $name
	 * @return Containers\Doctrine\EntityContainer
	 */
	public function addManyToMany($name, $label = NULL, $items = NULL, $size = NULL, array $criteria = array(), array $orderBy = NULL, $limit = NULL, $offset = NULL)
	{
		$ref = $this->entity->getReflection()->getProperty($name);

		if ($ref->hasAnnotation("Form")) {
			$ref = $ref->getAnnotation("Form");
			$class = $ref["targetEntity"];
			if (substr($class, 0, 1) != "\\") {
				$class = "\\" . $this->entity->getReflection()->getNamespaceName() . "\\" . $class;
			}
		} else {
			$ref = $ref->getAnnotation("ManyToMany");
			$class = $ref["targetEntity"];
			if (substr($class, 0, 1) != "\\") {
				$class = "\\" . $this->entity->getReflection()->getNamespaceName() . "\\" . $class;
			}
		}

		$items = $this->entityManager->getRepository($class)->findBy($criteria, $orderBy, $limit, $offset);

		$this[$name] = new Controls\ManyToMany($label, $items, $size);
		return $this[$name];
	}

}
