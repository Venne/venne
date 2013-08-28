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
use Nette\Http\FileUpload;
use Nette\InvalidArgumentException;
use Nette\Utils\Finder;
use Nette\Utils\Strings;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 * @ORM\Entity(repositoryClass="\CmsModule\Content\Repositories\FileRepository")
 * @ORM\Table(name="file", uniqueConstraints={@ORM\UniqueConstraint(
 *    name="path_idx", columns={"path"}
 * )})
 * @ORM\HasLifecycleCallbacks
 */
class FileEntity extends BaseFileEntity
{

	/**
	 * @var DirEntity
	 * @ORM\ManyToOne(targetEntity="DirEntity", inversedBy="files")
	 * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", onDelete="CASCADE")
	 */
	protected $parent;

	/** @ORM\Column(type="boolean") */
	protected $protected = FALSE;

	/** @var FileUpload|\SplFileInfo */
	protected $file;

	/** @var bool */
	protected $_oldProtected;

	/** @var string */
	protected $_oldPath;


	/**
	 * @ORM\PreFlush()
	 */
	public function preUpload()
	{
		if ($this->file) {
			if ($this->file instanceof FileUpload) {
				$this->setName($this->file->getSanitizedName());
			} else {
				$this->setName(Strings::webalize($this->file->getBasename(), '.'));
			}

			$this->generatePath();

			if ($this->_oldPath && $this->_oldPath !== $this->path) {
				@unlink($this->getFilePathBy($this->_oldProtected, $this->_oldPath));
			}

			if ($this->file instanceof FileUpload) {
				$this->file->move($this->getFilePath());
			} else {
				copy($this->file->getPathname(), $this->getFilePath());
			}
			return;
		}

		if (
			($this->_oldPath || $this->_oldProtected) &&
			($this->_oldPath != $this->path || $this->_oldProtected != $this->protected)
		) {
			$oldFilePath = $this->getFilePathBy($this->_oldProtected ? : $this->protected, $this->_oldPath ? : $this->path);
			if (file_exists($oldFilePath)) {
				rename($oldFilePath, $this->getFilePath());
			}
		}
	}


	/**
	 * @ORM\PreRemove()
	 */
	public function preRemove()
	{
		@unlink($this->getFilePath());

		// remove cache
		$dir = $this->publicDir . '/_cache';
		if (file_exists($dir)) {
			foreach (Finder::findFiles('*/*/*/' . $this->getName())->from($dir) as $file) {
				@unlink($file->getPathname());
			}
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


	public function getFilePath($withoutBasePath = FALSE)
	{
		return ($withoutBasePath ? '' : ($this->protected ? $this->protectedDir : $this->publicDir) . '/') . $this->path;
	}


	public function getFileUrl($withoutBasePath = FALSE)
	{
		return ($withoutBasePath ? '' : $this->publicUrl . '/') . $this->path;
	}


	/**
	 * @param FileUpload|\SplFileInfo $file
	 */
	public function setFile($file)
	{
		if (!$file instanceof FileUpload && !$file instanceof \SplFileInfo) {
			throw new InvalidArgumentException("File must be instance of 'FileUpload' OR 'SplFileInfo'. '" . get_class($file) . "' is given.");
		}

		if ($file instanceof FileUpload && !$file->isOk()) {
			return;
		}

		if (!$this->_oldPath && $this->path) {
			$this->_oldPath = $this->path;
			$this->_oldProtected = $this->protected;
		}

		$this->file = $file;
	}


	/**
	 * @return FileUpload|\SplFileInfo
	 */
	public function getFile()
	{
		return $this->file;
	}
}
