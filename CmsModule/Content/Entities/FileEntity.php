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

use CmsModule\Content\PermissionDeniedException;
use CmsModule\Security\Entities\RoleEntity;
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

	/**
	 * @var RoleEntity[]
	 * @ORM\ManyToMany(targetEntity="\CmsModule\Security\Entities\RoleEntity")
	 * @ORM\JoinTable(name="file_read")
	 **/
	protected $read;

	/** @var FileUpload|\SplFileInfo */
	protected $file;


	/**
	 * @ORM\PreFlush()
	 */
	public function preUpload()
	{
		if ($this->file) {
			if ($this->file instanceof FileUpload) {
				$this->setName($this->file->getSanitizedName());
			} else {
				$this->setName(trim(Strings::webalize($this->file->getBasename(), '.', FALSE), '.-'));
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
			($this->_oldPath || $this->_oldProtected !== NULL) &&
			($this->_oldPath != $this->path || $this->_oldProtected != $this->protected)
		) {
			$oldFilePath = $this->getFilePathBy($this->_oldProtected !== NULL ? $this->_oldProtected : $this->protected, $this->_oldPath ? : $this->path);

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


	/**
	 * @param $protected
	 * @param $path
	 * @return string
	 * @throws \CmsModule\Content\PermissionDeniedException
	 */
	public function getFilePathBy($protected, $path)
	{
		if (!$this->isAllowedToRead()) {
			throw new PermissionDeniedException;
		}

		return ($protected ? $this->protectedDir : $this->publicDir) . '/' . $path;
	}


	/**
	 * @param bool $withoutBasePath
	 * @return string
	 * @throws \CmsModule\Content\PermissionDeniedException
	 */
	public function getFilePath($withoutBasePath = FALSE)
	{
		if (!$this->isAllowedToRead()) {
			throw new PermissionDeniedException;
		}

		return ($withoutBasePath ? '' : ($this->protected ? $this->protectedDir : $this->publicDir) . '/') . $this->path;
	}


	/**
	 * @param bool $withoutBasePath
	 * @return string
	 * @throws \CmsModule\Content\PermissionDeniedException
	 */
	public function getFileUrl($withoutBasePath = FALSE)
	{
		if (!$this->isAllowedToRead()) {
			throw new PermissionDeniedException;
		}

		return ($withoutBasePath ? '' : $this->publicUrl . '/') . $this->path;
	}


	/**
	 * @param $file
	 * @throws \Nette\InvalidArgumentException
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

		$this->updated = new \DateTime;
	}


	/**
	 * @return FileUpload|\SplFileInfo
	 * @throws \CmsModule\Content\PermissionDeniedException
	 */
	public function getFile()
	{
		if (!$this->isAllowedToRead()) {
			throw new PermissionDeniedException;
		}

		return $this->file;
	}
}
