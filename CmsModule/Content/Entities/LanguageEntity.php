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

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 * @ORM\Entity(repositoryClass="\CmsModule\Content\Repositories\LanguageRepository")
 * @ORM\Table(name="language")
 */
class LanguageEntity extends \DoctrineModule\Entities\IdentifiedEntity
{

	/** @ORM\Column(type="string", unique=true, length=32) */
	protected $name;

	/** @ORM\Column(type="string", unique=true, length=32) */
	protected $short;

	/** @ORM\Column(type="string", unique=true, length=32) */
	protected $alias;

	/**
	 * @var PageEntity[]|ArrayCollection|array
	 * @ORM\ManyToMany(targetEntity="PageEntity", mappedBy="languages")
	 */
	protected $pages;


	public function __construct()
	{
		$this->name = "";
		$this->short = "";
		$this->alias = "";
		$this->pages = new ArrayCollection;
	}


	public function __toString()
	{
		return $this->name;
	}


	public function getName()
	{
		return $this->name;
	}


	public function setName($name)
	{
		$this->name = $name;
	}


	public function getShort()
	{
		return $this->short;
	}


	public function setShort($short)
	{
		$this->short = $short;
	}


	public function getAlias()
	{
		return $this->alias;
	}


	public function setAlias($alias)
	{
		$this->alias = $alias;
	}


	public function setPages($pages)
	{
		$this->pages = $pages;
	}


	/**
	 * @return array|PageEntity[]|\Doctrine\Common\Collections\ArrayCollection
	 */
	public function getPages()
	{
		return $this->pages;
	}
}
