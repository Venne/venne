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
use Nette\Utils\Finder;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 * @Entity(repositoryClass="\DoctrineModule\ORM\BaseRepository")
 * @Table(name="file")
 * @HasLifecycleCallbacks
 */
class FileEntity extends BaseFileEntity
{

	/**
	 * @var DirEntity
	 * @ManyToOne(targetEntity="DirEntity", inversedBy="files")
	 * @JoinColumn(name="parent_id", referencedColumnName="id", onDelete="CASCADE")
	 */
	protected $parent;

	/** @Column(type="boolean") */
	protected $protected;

	/**
	 * @var \Nette\Http\FileUpload
	 * @Column(type="boolean", nullable=true)
	 */
	protected $file;

	/** @var bool */
	protected $_oldProtected;

	/** @var string */
	protected $_oldPath;


	public function __construct()
	{
		parent::__construct();

		$this->protected = false;
	}


	/**
	 * @PreFlush()
	 */
	public function preUpload()
	{
		if ($this->file) {
			$this->setName($this->file->getSanitizedName());
			$this->generatePath();

			if ($this->_oldPath && $this->_oldPath !== $this->path) {
				@unlink($this->getFilePathBy($this->_oldProtected, $this->_oldPath));
			}

			$this->file->move($this->getFilePath());
			return;
		}

		if (
			($this->_oldPath || $this->_oldProtected) &&
			($this->_oldPath != $this->path || $this->_oldProtected != $this->protected)
		) {
			rename(
				$this->getFilePathBy($this->_oldProtected ? : $this->protected, $this->_oldPath ? : $this->path),
				$this->getFilePath()
			);
		}
	}


	/**
	 * @PreRemove()
	 */
	public function preRemove()
	{
		@unlink($this->getFilePath());

		// remove cache
		foreach(Finder::findFiles('*/*/*/' . $this->getName())->from($this->publicDir . '/_cache') as $file) {
			@unlink($file->getPathname());

		}
	}


	public function setProtected($protected)
	{
		if (!$this->_oldProtected) {
			$this->_oldProtected = $this->protected;
		}

		$this->protected = $protected;
	}


	public function getProtected()
	{
		return $this->protected;
	}


	public function getFilePathBy($protected, $path)
	{
		return ($protected ? $this->protectedDir : $this->publicDir) . '/' . $path;
	}


	public function getFilePath()
	{
		return ($this->protected ? $this->protectedDir : $this->publicDir) . '/' . $this->path;
	}


	public function getFileUrl()
	{
		return $this->publicUrl . '/' . $this->path;
	}


	/**
	 * @param \Nette\Http\FileUpload $file
	 */
	public function setFile(\Nette\Http\FileUpload $file)
	{
		if (!$file->isOk()) {
			return;
		}

		if(!$this->_oldPath && $this->path){
			$this->_oldPath = $this->path;
			$this->_oldProtected = $this->protected;
		}

		$this->file = $file;
	}


	/**
	 * @return \Nette\Http\FileUpload
	 */
	public function getFile()
	{
		return $this->file;
	}
}
