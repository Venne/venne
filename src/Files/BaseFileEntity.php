<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Files;

use Venne\System\Content\PermissionDeniedException;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Kdyby\Doctrine\Entities\BaseEntity;
use Nette\Security\User;
use Nette\Utils\Strings;
use Venne\Doctrine\Entities\IdentifiedEntityTrait;
use Venne\Security\RoleEntity;
use Venne\Security\UserEntity;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class BaseFileEntity extends BaseEntity
{

	use IdentifiedEntityTrait;

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
	 * @var UserEntity
	 * @ORM\ManyToOne(targetEntity="\Venne\Security\UserEntity")
	 * @ORM\JoinColumn(onDelete="SET NULL")
	 */
	protected $author;

	/**
	 * @var RoleEntity[]|ArrayCollection
	 * @ORM\ManyToMany(targetEntity="\Venne\Security\RoleEntity")
	 * @ORM\JoinTable(name="file_read")
	 **/
	protected $read;

	/**
	 * @var RoleEntity[]|ArrayCollection
	 * @ORM\ManyToMany(targetEntity="\Venne\Security\RoleEntity")
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
		$this->created = new \DateTime;
		$this->updated = new \DateTime;
		$this->read = new ArrayCollection;
		$this->write = new ArrayCollection;
	}


	/**
	 * @param BaseFileEntity $parent
	 */
	public function copyPermission(BaseFileEntity $parent = NULL)
	{
		$parent = $parent ? : $this->parent;

		if ($parent === NULL) {
			return;
		}

		if (!$this->user) {
			$this->user = $parent->user;
		}

		$this->protected = $parent->protected;
		$this->read->clear();
		$this->write->clear();

		foreach ($parent->read as $role) {
			$this->read->add($role);
		}

		foreach ($parent->write as $role) {
			$this->write->add($role);
		}
	}


	/**
	 * @param $name
	 * @throws \Venne\System\Content\PermissionDeniedException
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
	 * @throws \Venne\System\Content\PermissionDeniedException
	 */
	public function getName()
	{
		if (!$this->isAllowedToRead()) {
			throw new PermissionDeniedException;
		}

		return $this->name;
	}


	/**
	 * @param DirEntity $parent
	 * @throws \Venne\System\Content\PermissionDeniedException
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
	 * @return DirEntity
	 * @throws \Venne\System\Content\PermissionDeniedException
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


	/**
	 * @return bool
	 * @throws \Venne\System\Content\PermissionDeniedException
	 */
	public function getInvisible()
	{
		if (!$this->isAllowedToRead()) {
			throw new PermissionDeniedException;
		}

		return $this->invisible;
	}


	/**
	 * @param $protected
	 * @throws \Venne\System\Content\PermissionDeniedException
	 */
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


	/**
	 * @return bool
	 * @throws \Venne\System\Content\PermissionDeniedException
	 */
	public function getProtected()
	{
		if (!$this->isAllowedToRead()) {
			throw new PermissionDeniedException;
		}

		return $this->protected;
	}


	/**
	 * @param $read
	 * @throws \Venne\System\Content\PermissionDeniedException
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
	 * @return \Venne\Security\Entities\RoleEntity[]|ArrayCollection
	 * @throws \Venne\System\Content\PermissionDeniedException
	 */
	public function getRead()
	{
		if (!$this->isAllowedToRead()) {
			throw new PermissionDeniedException;
		}

		return $this->read;
	}


	/**
	 * @param $write
	 * @throws \Venne\System\Content\PermissionDeniedException
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
	 * @return \Venne\Security\Entities\RoleEntity[]|ArrayCollection
	 * @throws \Venne\System\Content\PermissionDeniedException
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
	 * @throws \Venne\System\Content\PermissionDeniedException
	 */
	public function getPath()
	{
		if (!$this->isAllowedToRead()) {
			throw new PermissionDeniedException;
		}

		return $this->path;
	}


	/**
	 * @throws \Venne\System\Content\PermissionDeniedException
	 */
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
	 * @throws \Venne\System\Content\PermissionDeniedException
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
	 * @throws \Venne\System\Content\PermissionDeniedException
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
	 * @throws \Venne\System\Content\PermissionDeniedException
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
	 * @throws \Venne\System\Content\PermissionDeniedException
	 */
	public function getUpdated()
	{
		if (!$this->isAllowedToRead()) {
			throw new PermissionDeniedException;
		}

		return $this->updated;
	}


	/**
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
	 * @return bool
	 */
	public function isAllowedToWrite()
	{
		if ($this->_isAllowedToWrite === NULL) {
			$this->_isAllowedToWrite = FALSE;

			if (!$this->author) {
				$this->_isAllowedToWrite = TRUE;
			} else if ($this->user) {
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
