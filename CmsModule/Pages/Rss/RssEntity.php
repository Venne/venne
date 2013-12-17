<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace CmsModule\Pages\Rss;

use CmsModule\Content\Entities\ExtendedRouteEntity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 * @ORM\Entity(repositoryClass="\CmsModule\Pages\Rss\RssRepository")
 * @ORM\Table(name="rss_rss")
 */
class RssEntity extends ExtendedRouteEntity
{

	const CACHE = 'Cms.RssEntity';

	/**
	 * @var string
	 * @ORM\Column(type="string")
	 **/
	protected $class;

	/**
	 * @var \CmsModule\Content\Entities\PageEntity[]|ArrayCollection
	 * @ORM\ManyToMany(targetEntity="\CmsModule\Content\Entities\PageEntity")
	 */
	protected $targetPages;

	/**
	 * @var int
	 * @ORM\Column(type="integer")
	 */
	protected $items = 20;


	protected function startup()
	{
		parent::startup();

		$this->targetPages = new ArrayCollection;
	}


	/**
	 * @param string $class
	 */
	public function setClass($class)
	{
		$this->class = $class;
	}


	/**
	 * @return string
	 */
	public function getClass()
	{
		return $this->class;
	}


	/**
	 * @param \CmsModule\Content\Entities\PageEntity[]|ArrayCollection $targetPages
	 */
	public function setTargetPages($targetPages)
	{
		$this->targetPages = $targetPages;
	}


	/**
	 * @return \CmsModule\Content\Entities\PageEntity[]|ArrayCollection
	 */
	public function getTargetPages()
	{
		return $this->targetPages;
	}


	/**
	 * @param int $items
	 */
	public function setItems($items)
	{
		$this->items = $items;
	}


	/**
	 * @return int
	 */
	public function getItems()
	{
		return $this->items;
	}

}

