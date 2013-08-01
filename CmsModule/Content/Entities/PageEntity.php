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
use Doctrine\ORM\UnitOfWork;
use Nette\Security\User;
use Nette\Utils\Strings;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 * @ORM\Entity(repositoryClass="\CmsModule\Content\Repositories\PageRepository")
 * @ORM\Table(name="page", indexes={
 * @ORM\Index(name="special_idx", columns={"special"}),
 * @ORM\Index(name="class_idx", columns={"class"}),
 * })
 *
 * @property RouteEntity $mainRoute
 * @property ArrayCollection|RouteEntity[] $routes
 * @property string $special
 * @property bool $secured
 */
class PageEntity extends TreeEntity implements IloggableEntity
{

	const CACHE = 'Cms.PageEntity';

	/**
	 * @var ArrayCollection|RouteEntity[]
	 * @ORM\OneToMany(targetEntity="\CmsModule\Content\Entities\RouteEntity", mappedBy="page", cascade={"persist", "remove", "detach"})
	 */
	protected $routes;

	/**
	 * @var RouteEntity
	 * @ORM\ManyToOne(targetEntity="\CmsModule\Content\Entities\RouteEntity", cascade={"persist", "remove", "detach"})
	 * @ORM\JoinColumn(name="route_id", referencedColumnName="id", onDelete="CASCADE")
	 */
	protected $mainRoute;

	/**
	 * @var LanguageEntity
	 * @ORM\ManyToOne(targetEntity="\CmsModule\Content\Entities\LanguageEntity")
	 */
	protected $language;

	/**
	 * @ORM\OneToOne(targetEntity="\CmsModule\Content\Entities\DirEntity", cascade={"all"})
	 * @ORM\JoinColumn(onDelete="SET NULL")
	 */
	protected $dir;

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
	 * @var string
	 * @ORM\Column(type="string", nullable=true)
	 */
	protected $navigationTitleRaw;

	/**
	 * @var bool
	 * @ORM\Column(type="boolean")
	 */
	protected $navigationShow = TRUE;

	/**
	 * @var string
	 * @ORM\Column(type="string", nullable=true, unique=true)
	 */
	protected $special;

	/**
	 * @var string
	 * @ORM\Column(type="string")
	 */
	protected $class;

	/**
	 * @var bool
	 * @ORM\Column(type="boolean")
	 */
	protected $secured = FALSE;

	/**
	 * @var PermissionEntity[]|ArrayCollection
	 * @ORM\OneToMany(targetEntity="PermissionEntity", mappedBy="page", indexBy="name", fetch="EXTRA_LAZY", orphanRemoval=true, cascade={"all"})
	 */
	protected $permissions;

	/** @var array */
	protected $_isAlowed = array();


	/**
	 * @param ExtendedPageEntity $page
	 */
	public function __construct(ExtendedPageEntity $page)
	{
		parent::__construct();

		$this->routes = new ArrayCollection;
		$this->permissions = new ArrayCollection;
		$this->created = new \DateTime;
		$this->updated = new \DateTime;
		$this->class = get_class($page);

		$this->dir = new DirEntity;
		$this->dir->setInvisible(TRUE);
		$this->dir->setName(Strings::webalize(get_class($this)) . Strings::random());
	}


	/**
	 * @return string
	 */
	public function __toString()
	{
		return $this->getMainRoute()->getName() . ' (' . $this->getMainRoute()->getUrl() . ')';
	}


	/**
	 * Set this page as root.
	 */
	public function setAsRoot()
	{
		$main = $this->getRoot();
		$this->setParent(NULL);
		$main->setParent($this);

		foreach ($main->children as $item) {
			$item->setParent($this);
		}
	}


	/**
	 * @param $parent
	 */
	public function setParent(PageEntity $parent = NULL, $setPrevious = NULL, PageEntity $previous = NULL)
	{
		parent::setParent($parent, $setPrevious, $previous);

		$this->mainRoute->parent = $this->parent && $this->parent->mainRoute ? $this->parent->mainRoute : NULL;
		$this->generateUrl();
	}


	/**
	 * @param $parent
	 */
	public function setVirtualParent(PageEntity $parent = NULL)
	{
		parent::setVirtualParent($parent);

		$this->mainRoute->parent = $this->virtualParent && $this->virtualParent->mainRoute ? $this->virtualParent->mainRoute : NULL;
		$this->generateUrl();
	}


	/**
	 * Generate URL.
	 */
	protected function generateUrl($recursively = TRUE)
	{
		foreach ($this->routes as $route) {
			$route->generateUrl($recursively);
		}
	}


	/**
	 * @return LanguageEntity
	 */
	public function getLanguage()
	{
		return $this->language;
	}


	/**
	 * @param LanguageEntity $language
	 */
	public function setLanguage(LanguageEntity $language = NULL)
	{
		$this->language = $language;

		foreach ($this->routes as $route) {
			$route->setLanguage($language);
		}
	}


	/**
	 * @return DirEntity
	 */
	public function getDir()
	{
		return $this->dir;
	}


	/**
	 * @param $children
	 */
	public function setChildren($children)
	{
		$this->children = $children;
	}


	/**
	 * @return ArrayCollection|PageEntity[]
	 */
	public function getChildren()
	{
		return $this->children;
	}


	/**
	 * @return ArrayCollection|RouteEntity[]
	 */
	public function getRoutes()
	{
		return $this->routes;
	}


	/**
	 * @param $routes
	 */
	public function setRoutes($routes)
	{
		$this->routes = $routes;
	}


	/**
	 * @return RouteEntity
	 */
	public function getMainRoute()
	{
		return $this->mainRoute;
	}


	/**
	 * @param $mainRoute
	 */
	public function setMainRoute($mainRoute)
	{
		$this->mainRoute = $mainRoute;
	}


	/**
	 * @return DateTime|\Nette\DateTime
	 */
	public function getCreated()
	{
		return $this->created;
	}


	/**
	 * @return DateTime|\Nette\DateTime
	 */
	public function getUpdated()
	{
		return $this->updated;
	}


	/**
	 * @return DateTime
	 */
	public function getExpired()
	{
		return $this->expired;
	}


	/**
	 * @param $expired
	 */
	public function setExpired($expired)
	{
		$this->expired = $expired;
	}


	/**
	 * @param  $navigationShow
	 */
	public function setNavigationShow($navigationShow)
	{
		$this->navigationShow = $navigationShow;
	}


	/**
	 * @return
	 */
	public function getNavigationShow()
	{
		return $this->navigationShow;
	}


	/**
	 * @param  $navigationTitleRaw
	 */
	public function setNavigationTitleRaw($navigationTitleRaw)
	{
		$this->navigationTitleRaw = $navigationTitleRaw;
	}


	/**
	 * @return
	 */
	public function getNavigationTitleRaw()
	{
		return $this->navigationTitleRaw;
	}


	/**
	 * @return
	 */
	public function getNavigationTitle()
	{
		return $this->navigationTitleRaw !== NULL ? $this->navigationTitleRaw : $this->getMainRoute()->name;
	}


	/**
	 * @param mixed $special
	 */
	public function setSpecial($special)
	{
		$this->special = $special;
	}


	/**
	 * @return mixed
	 */
	public function getSpecial()
	{
		return $this->special;
	}


	/**
	 * @return string
	 */
	public function getClass()
	{
		return $this->class;
	}


	/**
	 * @param bool $secured
	 */
	public function setSecured($secured)
	{
		$this->secured = $secured;
	}


	/**
	 * @return bool
	 */
	public function getSecured()
	{
		return $this->secured;
	}


	/**
	 * @param PermissionEntity[] $permissions
	 */
	public function setPermissions($permissions)
	{
		$this->permissions = $permissions;
		$this->_isAlowed = array();
	}


	/**
	 * @return PermissionEntity[]|ArrayCollection
	 */
	public function getPermissions()
	{
		return $this->permissions;
	}


	/**
	 * @param User $user
	 * @param $permission
	 * @return bool
	 */
	public function isAllowed(User $user, $permission)
	{
		if (!isset($this->_isAlowed[$user->id][$permission])) {

			if (!isset($this->_isAlowed[$user->id])) {
				$this->_isAlowed[$user->id] = array();
			}

			if (isset($this->permissions[$permission])) {
				$permissionEntity = $this->permissions[$permission];

				if (!$user->isLoggedIn()) {
					$this->_isAlowed[$user->id][$permission] = FALSE;
					return FALSE;
				}

				if ($permissionEntity->getAll()) {
					$this->_isAlowed[$user->id][$permission] = TRUE;
					return TRUE;
				}

				foreach ($user->getRoles() as $role) {
					if (isset($permissionEntity->roles[$role])) {
						$this->_isAlowed[$user->id][$permission] = TRUE;
						return TRUE;
					}
				}
			}
			$this->_isAlowed[$user->id][$permission] = FALSE;
		}

		return $this->_isAlowed[$user->id][$permission];
	}


	public function log(LogEntity $logEntity, UnitOfWork $unitOfWork, $action)
	{
		$changeSet = $unitOfWork->getEntityChangeSet($this);
		$logEntity->setPage($this);

		if (count($changeSet) === 1 && isset($changeSet['published'])) {
			if ($changeSet['published'][1] === TRUE) {
				$logEntity->setMessage('Page has been published');
			} else {
				$logEntity->setMessage('Page has been unpublished');
			}
		}
	}
}
