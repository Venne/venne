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

use CmsModule\Content\Entities\RouteEntity;
use CmsModule\Pages\Tags\TagRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\PersistentCollection;
use FormsModule\Controls\TagInput;
use FormsModule\Controls\TagsInput;
use Nette\Utils\Strings;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class TagsControl extends TagsInput
{

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
		$tags = $this->getTagRepository()->createQueryBuilder('a')
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
		$repository = $this->getTagRepository();
		$collection = new ArrayCollection;

		foreach (parent::getValue() as $key => $tag) {
			if (!$tag) {
				continue;
			}
			if (($entity = $repository->findOneBy(array('name' => $tag))) === NULL) {
				$entity = $repository->createNew();
				$entity->setName($tag);
			}
			$collection[$key] = $entity;
		}

		return $collection;
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
	 * @return TagRepository
	 */
	private function getTagRepository()
	{
		return $this->form->presenter->context->getByType('CmsModule\Pages\Tags\TagRepository');
	}
}
