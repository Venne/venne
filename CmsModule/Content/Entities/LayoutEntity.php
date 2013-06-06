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
 * @ORM\Entity(repositoryClass="\CmsModule\Content\Repositories\LayoutRepository")
 * @ORM\Table(name="layout")
 */
class LayoutEntity extends \DoctrineModule\Entities\NamedEntity
{

	/**
	 * @var string
	 * @ORM\Column(type="string")
	 */
	protected $file;

	/**
	 * @var RouteEntity[]
	 * @ORM\OneToMany(targetEntity="RouteEntity", mappedBy="layout", fetch="EXTRA_LAZY")
	 */
	protected $routes;

	/**
	 * @var bool
	 * @ORM\Column(type="boolean")
	 */
	protected $locked = FALSE;


	public function __construct()
	{
		parent::__construct();

		$this->routes = new ArrayCollection;
	}


	/**
	 * @param string $file
	 */
	public function setFile($file)
	{
		$this->file = $file;
	}


	/**
	 * @return string
	 */
	public function getFile()
	{
		return $this->file;
	}


	public function setRoutes($routes)
	{
		$this->routes = $routes;
	}


	public function getRoutes()
	{
		return $this->routes;
	}


	/**
	 * @param boolean $locked
	 */
	public function setLocked($locked)
	{
		$this->locked = $locked;
	}


	/**
	 * @return boolean
	 */
	public function getLocked()
	{
		return $this->locked;
	}
}
