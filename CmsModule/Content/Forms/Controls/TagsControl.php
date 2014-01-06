<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 *
 * Copyright (c) 2004, 2011 David Grudl (http://davidgrudl.com)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace CmsModule\Content\Forms\Controls;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\PersistentCollection;
use DoctrineModule\Repositories\BaseRepository;
use FormsModule\Controls\TagsInput;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class TagsControl extends TagsInput
{

	/** @var array */
	private $_values;

	/** @var BaseRepository */
	private $_repository;


	public function __construct($label = NULL, $cols = NULL, $maxLength = NULL)
	{
		parent::__construct($label, $cols, $maxLength);

		$this->setSuggestCallback($this->suggestTags);
		$this->setJoiner(';');
		$this->setDelimiter('[;]+');
	}


	/**
	 * @param $query
	 * @return array
	 */
	public function suggestTags($query)
	{
		$tags = $this->getRepository()->createQueryBuilder('a')
			->select('a.name')
			->andWhere('a.name LIKE :name')->setParameter('name', "%{$query}%")
			->setMaxResults(50)
			->getQuery()->getScalarResult();

		$ret = array();

		foreach ($tags as $tag) {
			$ret[$tag['name']] = $tag['name'];
		}

		return $ret;
	}


	public function getValue()
	{
		if ($this->_values === NULL) {
			$repository = $this->getRepository();
			$this->_values = array();

			foreach (parent::getValue() as $key => $tag) {
				if (!$tag) {
					continue;
				}
				if (($entity = $repository->findOneBy(array('name' => $tag))) === NULL) {
					$entity = $repository->createNew();
					$entity->setName($tag);
				}
				$this->_values[$key] = $entity;
			}
		}

		return $this->_values;
	}


	public function setValue($value)
	{
		return parent::setValue($this->objectsToArrayStrings($value));
	}


	public function setDefaultValue($value)
	{
		return parent::setDefaultValue($this->objectsToArrayStrings($value));
	}


	private function objectsToArrayStrings($value)
	{
		$tags = array();

		if ($value instanceof ArrayCollection || $value instanceof PersistentCollection) {
			$value = $value->toArray();
		} else {
			$value = (array)$value;
		}

		foreach ($value as $tag) {
			if (!$tag) {
				continue;
			}
			$tags[] = is_object($tag) ? $tag->getName() : $tag;
		}

		return $tags;
	}


	/**
	 * @return BaseRepository
	 */
	private function getRepository()
	{
		if (!$this->_repository) {
			$ref = $this->getParent()->data->getReflection()->getProperty($this->name)->getAnnotation('ORM\\ManyToMany');

			$class = $ref['targetEntity'];
			if (substr($class, 0, 1) != '\\') {
				$class = '\\' . $this->getParent()->data->getReflection()->getNamespaceName() . '\\' . $class;
			}

			$this->_repository = $this->form->mapper->entityManager->getRepository($class);
		}

		return $this->_repository;
	}
}
