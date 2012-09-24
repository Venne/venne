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
use Doctrine\Common\Collections\ArrayCollection;
use Nette\Utils\Strings;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 * @Entity(repositoryClass="\CmsModule\Content\Repositories\DirRepository")
 * @Table(name="directory")
 * @HasLifecycleCallbacks
 */
class DirEntity extends BaseFileEntity
{

	/**
	 * @var ArrayCollection|DirEntity[]
	 * @OneToMany(targetEntity="DirEntity", mappedBy="parent")
	 */
	protected $childrens;

	/**
	 * @var ArrayCollection|FileEntity[]
	 * @OneToMany(targetEntity="FileEntity", mappedBy="parent")
	 */
	protected $files;


	function __construct()
	{
		parent::__construct();

		$this->childrens = new ArrayCollection;
		$this->files = new ArrayCollection;
	}


	public function __toString()
	{
		$ret = array();

		$parent = $this;
		while($parent){
			$ret[] = $parent->name;
			$parent = $parent->parent;
		}

		return implode('/', array_reverse($ret));
	}


	/**
	 * @PreFlush()
	 */
	public function preUpdate()
	{
		$protectedPath = $this->protectedDir . '/' . $this->path;
		$publicPath = $this->publicDir . '/' . $this->path;

		if($this->_oldPath){
			$oldProtectedPath = $this->protectedDir . '/' . $this->_oldPath;
			$oldPublicPath = $this->publicDir . '/' . $this->_oldPath;

			rename($oldProtectedPath, $protectedPath);
			rename($oldPublicPath, $publicPath);
			return;
		}

		umask(0000);
		if(!file_exists($protectedPath)){
			@mkdir($protectedPath, 0777, true);
		}

		if(!file_exists($publicPath)){
			@mkdir($publicPath, 0777, true);
		}
	}

	/**
	 * @PreRemove()
	 */
	public function preRemove()
	{
		$protectedPath = $this->protectedDir . '/' . $this->path;
		$publicPath = $this->publicDir . '/' . $this->path;

		@rmdir($protectedPath);
		@rmdir($publicPath);
	}


	/**
	 * @param string $childrens
	 */
	public function setChildrens(ArrayCollection $childrens)
	{
		$this->childrens = $childrens;
	}

	/**
	 * @return \Doctrine\Common\Collections\ArrayCollection
	 */
	public function getChildrens()
	{
		return $this->childrens;
	}


	public function setFiles($files)
	{
		$this->files = $files;
	}

	public function getFiles()
	{
		return $this->files;
	}


	public function generatePath()
	{
		parent::generatePath();

		foreach ($this->childrens as $item) {
			$item->generatePath();
		}

		foreach ($this->files as $item) {
			$item->generatePath();
		}
	}


}
