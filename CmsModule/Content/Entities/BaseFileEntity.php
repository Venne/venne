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
use CmsModule\Pages\Users\UserEntity;
use CmsModule\Security\Entities\RoleEntity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Nette\Security\User;
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
	protected $name = '';

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
	protected $invisible = FALSE;

	/**
	 * @var bool
	 * @ORM\Column(type="boolean")
	 */
	protected $protected = FALSE;

	/**
	 * @var \DateTime
	 * @ORM\Column(type="datetime")
	 */
	protected $created;

	/**
	 * @var \DateTime
	 * @ORM\Column(type="datetime")
	 */
	protected $updated;

	/**
	 * @var \CmsModule\Pages\Users\UserEntity
	 * @ORM\ManyToOne(targetEntity="\CmsModule\Pages\Users\UserEntity")
	 * @ORM\JoinColumn(onDelete="SET NULL")
	 */
	protected $author;

	/**
	 * @var RoleEntity[]|ArrayCollection
	 * @ORM\ManyToMany(targetEntity="\CmsModule\Security\Entities\RoleEntity")
	 * @ORM\JoinTable(name="file_read")
	 **/
	protected $read;

	/**
	 * @var RoleEntity[]|ArrayCollection
	 * @ORM\ManyToMany(targetEntity="\CmsModule\Security\Entities\RoleEntity")
	 * @ORM\JoinTable(name="file_write")
	 **/
	protected $write;

	/** @var string */
	protected $publicDir;

	/** @var string */
	protected $protectedDir;

	/** @var string */
	protected $publicUrl;

	/** @var User */
	protected $user;

	/** @var string */
	protected $_oldPath;

	/** @var bool */
	protected $_oldProtected;

	/** @var bool */
	private $_isAllowedToWrite;

	/** @var bool */
	private $_isAllowedToRead;


	public function __construct()
	{
		parent::__construct();

		$this->created = new \DateTime;
		$this->updated = new \DateTime;
		$this->read = new ArrayCollection;
		$this->write = new ArrayCollection;
	}


	/**
	 * @param string $name
	 */
	public function setName($name)
	{
		if ($this->name == $name) {
			return;
		}

		if (!$this->isAllowedToWrite()) {
			throw new PermissionDeniedException;
		}

		$this->name = $name;
		$this->generatePath();
		$this->updated = new \DateTime;
	}


	/**
	 * @return string
	 */
	public function getName()
	{
		if (!$this->isAllowedToRead()) {
			throw new PermissionDeniedException;
		}

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

		if (!$this->isAllowedToWrite()) {
			throw new PermissionDeniedException;
		}

		$this->parent = $parent;
		$this->generatePath();
	}


	/**
	 * @return string
	 */
	public function getParent()
	{
		if (!$this->isAllowedToRead()) {
			throw new PermissionDeniedException;
		}

		return $this->parent;
	}


	public function setInvisible($invisible)
	{
		if ($this->invisible == $invisible) {
			return;
		}

		if (!$this->isAllowedToWrite()) {
			throw new PermissionDeniedException;
		}

		$this->invisible = $invisible;
		$this->updated = new \DateTime;
	}


	public function getInvisible()
	{
		if (!$this->isAllowedToRead()) {
			throw new PermissionDeniedException;
		}

		return $this->invisible;
	}


	public function setProtected($protected)
	{
		if ($this->protected == $protected) {
			return;
		}

		if (!$this->isAllowedToWrite()) {
			throw new PermissionDeniedException;
		}

		if ($this->_oldProtected === NULL) {
			$this->_oldProtected = $this->protected;
		}

		$this->protected = $protected;
	}


	public function getProtected()
	{
		if (!$this->isAllowedToRead()) {
			throw new PermissionDeniedException;
		}

		return $this->protected;
	}


	/**
	 * @param RoleEntity[] $read
	 */
	public function setRead($read)
	{
		if ((array)$this->read == (array)$read) {
			return;
		}

		if (!$this->isAllowedToWrite()) {
			throw new PermissionDeniedException;
		}

		$this->read = $read;
	}


	/**
	 * @return \CmsModule\Security\Entities\RoleEntity[]|ArrayCollection
	 */
	public function getRead()
	{
		if (!$this->isAllowedToRead()) {
			throw new PermissionDeniedException;
		}

		return $this->read;
	}


	/**
	 * @param RoleEntity[] $write
	 */
	public function setWrite($write)
	{
		if ((array)$this->write == (array)$write) {
			return;
		}

		if (!$this->isAllowedToWrite()) {
			throw new PermissionDeniedException;
		}

		$this->write = $write;
	}


	/**
	 * @return \CmsModule\Security\Entities\RoleEntity[]|ArrayCollection
	 */
	public function getWrite()
	{
		if (!$this->isAllowedToRead()) {
			throw new PermissionDeniedException;
		}

		return $this->write;
	}


	/**
	 * @return string
	 */
	public function getPath()
	{
		if (!$this->isAllowedToRead()) {
			throw new PermissionDeniedException;
		}

		return $this->path;
	}


	public function generatePath()
	{
		if (!$this->isAllowedToWrite()) {
			throw new PermissionDeniedException;
		}

		$old = $this->path;

		if ($this->parent && $this->parent instanceof \Doctrine\ORM\Proxy\Proxy) {
			$this->parent->__load();
		}

		$this->path = ($this->parent ? $this->parent->path . '/' : '') . Strings::webalize($this->name, '.', FALSE);

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
		$this->updated = new \DateTime;
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


	/**
	 * @param \Nette\Security\User $user
	 */
	public function setUser(User $user)
	{
		if ($this->user === $user) {
			return;
		}

		if ($this->author === NULL && $user->identity instanceof UserEntity) {
			$this->author = $user->identity;
			$this->updated = new \DateTime;
		}

		$this->user = $user;
		$this->_isAllowedToRead = NULL;
		$this->_isAllowedToWrite = NULL;
	}


	/**
	 * @param UserEntity $author
	 */
	public function setAuthor(UserEntity $author = NULL)
	{
		if ($this->author === $author) {
			return;
		}

		if (!$this->isAllowedToWrite()) {
			throw new PermissionDeniedException;
		}

		$this->author = $author;
		$this->updated = new \DateTime;
	}


	/**
	 * @return UserEntity
	 */
	public function getAuthor()
	{
		if (!$this->isAllowedToRead()) {
			throw new PermissionDeniedException;
		}

		return $this->author;
	}


	/**
	 * @return \DateTime
	 */
	public function getCreated()
	{
		if (!$this->isAllowedToRead()) {
			throw new PermissionDeniedException;
		}

		return $this->created;
	}


	/**
	 * @return \DateTime
	 */
	public function getUpdated()
	{
		if (!$this->isAllowedToRead()) {
			throw new PermissionDeniedException;
		}

		return $this->updated;
	}


	/**
	 * @param User $user
	 * @return bool
	 */
	public function isAllowedToRead()
	{
		if ($this->_isAllowedToRead === NULL) {
			$this->_isAllowedToRead = FALSE;

			if (!$this->protected) {
				$this->_isAllowedToRead = TRUE;
			} else if ($this->user->isInRole('admin')) {
				$this->_isAllowedToRead = TRUE;
			} else {
				foreach ($this->read as $role) {
					if ($this->user->isInRole($role->getName())) {
						$this->_isAllowedToRead = TRUE;
					}
				}
			}
		}

		return $this->_isAllowedToRead;
	}


	/**
	 * @param User $user
	 * @return bool
	 */
	public function isAllowedToWrite()
	{
		if ($this->_isAllowedToWrite === NULL) {
			$this->_isAllowedToWrite = FALSE;

			if (!$this->author) {
				$this->_isAllowedToWrite = TRUE;
			} else if ($this->user)  {
				if ($this->author === $this->user->identity) {
					$this->_isAllowedToWrite = TRUE;
				} else if ($this->user->isInRole('admin')) {
					$this->_isAllowedToWrite = TRUE;
				} else {
					foreach ($this->read as $role) {
						if ($this->user->isInRole($role->getName())) {
							$this->_isAllowedToWrite = TRUE;
						}
					}
				}
			}
		}

		return $this->_isAllowedToWrite;
	}
}
