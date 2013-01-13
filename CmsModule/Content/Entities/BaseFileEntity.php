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

use Venne;
use Doctrine\ORM\Mapping as ORM;
use Nette\Utils\Strings;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class BaseFileEntity extends \DoctrineModule\Entities\IdentifiedEntity
{

	/**
	 * @var string
	 * @ORM\Column(type="string")
	 */
	protected $name;

	/**
	 * @var string
	 * @ORM\Column(type="string")
	 */
	protected $path;

	/**
	 * @var DirEntity
	 * @ORM\ManyToOne(targetEntity="DirEntity", inversedBy="children")
	 * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", onDelete="CASCADE")
	 */
	protected $parent;

	/**
	 * @var bool
	 * @ORM\Column(type="boolean")
	 */
	protected $invisible;

	/** @var string */
	protected $publicDir;

	/** @var string */
	protected $protectedDir;

	/** @var string */
	protected $publicUrl;

	/** @var string */
	protected $_oldPath;


	public function __construct()
	{
		$this->invisible = false;
		$this->name = '';
	}


	/**
	 * @param string $name
	 */
	public function setName($name)
	{
		if ($this->name == $name) {
			return;
		}

		$this->name = $name;
		$this->generatePath();
	}


	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}


	/**
	 * @param string $parent
	 */
	public function setParent(DirEntity $parent = NULL)
	{
		if ($this->parent == $parent) {
			return;
		}

		$this->parent = $parent;
		$this->generatePath();
	}


	/**
	 * @return string
	 */
	public function getParent()
	{
		return $this->parent;
	}


	public function setInvisible($invisible)
	{
		$this->invisible = $invisible;
	}


	public function getInvisible()
	{
		return $this->invisible;
	}


	/**
	 * @return string
	 */
	public function getPath()
	{
		return $this->path;
	}


	public function generatePath()
	{
		$old = $this->path;

		if ($this->parent && $this->parent instanceof \Doctrine\ORM\Proxy\Proxy) {
			$this->parent->__load();
		}

		$this->path = ($this->parent ? $this->parent->path . '/' : '') . Strings::webalize($this->name, '.');

		if ($this->path == $old) {
			return;
		}

		if ($this->id) {
			if (!$this->_oldPath && $old != $this->path) {
				$this->_oldPath = $old;
			} else if ($this->_oldPath && $this->_oldPath == $this->path) {
				$this->_oldPath = NULL;
			}
		}
	}


	/** Setters for paths */

	/**
	 * @param string $protectedDir
	 */
	public function setProtectedDir($protectedDir)
	{
		$this->protectedDir = $protectedDir;
	}


	public function setPublicDir($publicDir)
	{
		$this->publicDir = $publicDir;
	}


	/**
	 * @param string $publicUrl
	 */
	public function setPublicUrl($publicUrl)
	{
		$this->publicUrl = $publicUrl;
	}
}
