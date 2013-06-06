<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace CmsModule\Content\Entities;

use Doctrine\ORM\Mapping as ORM;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class TreeEntity extends \DoctrineModule\Entities\IdentifiedEntity
{

	/**
	 * @var TreeEntity
	 * @ORM\ManyToOne(targetEntity="\CmsModule\Content\Entities\PageEntity", inversedBy="children")
	 * @ORM\JoinColumn(onDelete="CASCADE")
	 */
	protected $parent;

	/**
	 * @ORM\ManyToOne(targetEntity="\CmsModule\Content\Entities\PageEntity", inversedBy="next")  # ManyToOne is hack for prevent '1062 Duplicate entry update'
	 */
	protected $previous;

	/**
	 * @ORM\OneToOne(targetEntity="\CmsModule\Content\Entities\PageEntity", mappedBy="previous")
	 */
	protected $next;

	/** @ORM\Column(type="integer") */
	protected $position;

	/**
	 * @ORM\OneToMany(targetEntity="\CmsModule\Content\Entities\PageEntity", mappedBy="parent", cascade={"persist", "remove", "detach"}, fetch="EXTRA_LAZY")
	 * @ORM\OrderBy({"position" = "ASC"})
	 */
	protected $children;

	/**
	 * @ORM\OneToMany(targetEntity="\CmsModule\Content\Entities\PageEntity", mappedBy="virtualParent", cascade={"persist"})
	 */
	protected $virtualChildren;

	/**
	 * @ORM\ManyToOne(targetEntity="\CmsModule\Content\Entities\PageEntity", inversedBy="virtualChildren")
	 * @ORM\JoinColumn(onDelete="CASCADE")
	 */
	protected $virtualParent;


	/**
	 * @param $type
	 */
	public function __construct()
	{
		$this->position = 1;
		$this->children = new \Doctrine\Common\Collections\ArrayCollection;
	}


	/**
	 * @PostRemove()
	 */
	public function onPostRemove()
	{
		$this->removeFromPosition();
	}


	/**
	 * @return mixed
	 */
	public function getParent()
	{
		return $this->parent;
	}


	public function removeFromPosition()
	{
		$next = $this->next;
		$previous = $this->previous;

		if ($next) {
			$next->previous = $previous;
		}
		if ($previous) {
			$previous->next = $next;
		}

		if ($next) {
			$this->moveUp($next);
		}

		if ($this->parent) {
			foreach ($this->parent->children as $key => $item) {
				if ($item->id === $this->id) {
					$this->parent->children->remove($key);
					break;
				}
			}
		}

		if ($this->mainRoute->parent) {
			foreach ($this->mainRoute->parent->getChildren() as $key => $route) {
				if ($route->id === $this->mainRoute->id) {
					$this->mainRoute->parent->getChildren()->remove($key);
				}
			}
		}

		$this->previous = NULL;
		$this->parent = NULL;
		$this->next = NULL;
		$this->position = 1;
	}


	protected function moveUp(PageEntity $entity)
	{
		do {
			$entity->position--;
			$entity = $entity->next;
		} while ($entity);
	}


	protected function moveDown(PageEntity $entity)
	{
		do {
			$entity->position++;
			$entity = $entity->next;
		} while ($entity);
	}


	public function getRoot(PageEntity $entity = NULL)
	{
		$entity = $entity ? : $this;

		while ($entity->parent) {
			$entity = $entity->parent;
		}

		while ($entity->previous) {
			$entity = $entity->previous;
		}

		return $entity;
	}


	/**
	 * @param $parent
	 */
	public function setParent(PageEntity $parent = NULL, $setPrevious = NULL, PageEntity $previous = NULL)
	{
		if ($parent == $this->parent && !$setPrevious) {
			return;
		}

		if (!$parent && !$this->next && !$this->previous && !$this->parent && !$setPrevious) {
			return;
		}

		$oldParent = $this->parent;
		$oldPrevious = $this->previous;
		$oldNext = $this->next;

		// remove from position
		$this->removeFromPosition();


		if ($parent) {
			$this->parent = $parent;

			if ($setPrevious) {
				if ($previous) {
					if ($previous->next) {
						$this->moveDown($previous->next);
						$previous->next->previous = $this;
					}
					$this->next = $previous->next;

					$this->previous = $previous;
					$this->position = $this->previous->position + 1;
					$previous->next = $this;
				} else {
					$this->next = $parent->getChildren()->first() ? : NULL;
					$this->previous = NULL;
					if ($this->next) {
						$this->next->previous = $this;
						$this->moveDown($this->next);
					}
					$this->position = 1;
				}
			} else {
				$this->previous = $parent->getChildren()->last() ? : NULL;
				$this->next = NULL;
				if ($this->previous) {
					$this->previous->next = $this;
					$this->position = $this->previous->position + 1;
				}
			}

			$parent->children[] = $this;
		} else {
			if ($setPrevious) {
				if ($previous) {
					if ($previous->next) {
						$this->moveDown($previous->next);
						$previous->next->previous = $this;
					}
					$this->next = $previous->next;

					$this->previous = $previous;
					$this->position = $this->previous->position + 1;
					$previous->next = $this;
				} else {
					$this->next = $this->getRoot($oldNext ? : ($oldParent ? : ($oldPrevious)));
					if ($this->next) {
						$this->next->previous = $this;
						$this->moveDown($this->next);
					}
					$this->position = 1;
				}
			} else {
				$this->parent = NULL;
				$this->previous = NULL;
				$this->next = NULL;
				$this->position = 1;
			}
		}
	}


	/**
	 * @param $children
	 */
	public function setChildren($children)
	{
		$this->children = $children;
	}


	/**
	 * @return \Doctrine\Common\Collections\ArrayCollection
	 */
	public function getChildren()
	{
		return $this->children;
	}


	public function getPrevious()
	{
		return $this->previous;
	}


	public function setNext(TreeEntity $next = NULL)
	{
		$this->next = $next;
	}


	public function setPrevious(TreeEntity $previous = NULL)
	{
		$this->previous = $previous;
	}


	public function getNext()
	{
		return $this->next;
	}


	public function setPosition($position)
	{
		$this->position = $position;
	}


	public function getPosition()
	{
		return $this->position;
	}


	public function setVirtualChildren($virtualChildren)
	{
		$this->virtualChildren = $virtualChildren;
	}


	public function getVirtualChildren()
	{
		return $this->virtualChildren;
	}


	public function setVirtualParent(PageEntity $virtualParent = NULL)
	{
		$this->virtualParent = $virtualParent;
	}


	public function getVirtualParent()
	{
		return $this->virtualParent;
	}
}

