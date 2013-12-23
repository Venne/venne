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
use DoctrineModule\Entities\IdentifiedEntity;
use Nette\Callback;
use Nette\InvalidArgumentException;
use Nette\Security\User;
use Nette\Utils\Strings;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 * @ORM\Entity(repositoryClass="\CmsModule\Content\Repositories\PageRepository")
 * @ORM\EntityListeners({"\CmsModule\Content\Listeners\ExtendedPageListener"})
 * @ORM\HasLifecycleCallbacks
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
class PageEntity extends IdentifiedEntity implements IloggableEntity
{

	const CACHE = 'Cms.PageEntity';

	/**
	 * @var PageEntity
	 * @ORM\ManyToOne(targetEntity="\CmsModule\Content\Entities\PageEntity", inversedBy="children")
	 * @ORM\JoinColumn(onDelete="CASCADE")
	 */
	protected $parent;

	/**
	 * @var PageEntity
	 * @ORM\ManyToOne(targetEntity="\CmsModule\Content\Entities\PageEntity", inversedBy="next", fetch="EAGER")  # ManyToOne is hack for prevent '1062 Duplicate entry update'
	 */
	protected $previous;

	/**
	 * @var PageEntity
	 * @ORM\OneToOne(targetEntity="\CmsModule\Content\Entities\PageEntity", mappedBy="previous", fetch="EAGER")
	 */
	protected $next;

	/** @ORM\Column(type="integer") */
	protected $position = 1;

	/**
	 * @var PageEntity[]
	 * @ORM\OneToMany(targetEntity="\CmsModule\Content\Entities\PageEntity", mappedBy="parent", cascade={"persist", "remove", "detach"}, fetch="EXTRA_LAZY")
	 * @ORM\OrderBy({"position" = "ASC"})
	 */
	protected $children;

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
	 * @var bool
	 * @ORM\Column(type="boolean")
	 */
	protected $published = FALSE;

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
	 * @var bool
	 * @ORM\Column(type="boolean")
	 */
	protected $adminSecured = FALSE;

	/**
	 * @var PermissionEntity[]|ArrayCollection
	 * @ORM\OneToMany(targetEntity="PermissionEntity", mappedBy="page", indexBy="name", orphanRemoval=true, cascade={"all"})
	 */
	protected $permissions;

	/**
	 * @var AdminPermissionEntity[]|ArrayCollection
	 * @ORM\OneToMany(targetEntity="AdminPermissionEntity", mappedBy="page", indexBy="name", orphanRemoval=true, cascade={"all"})
	 */
	protected $adminPermissions;

	/** @var array */
	protected $_isAllowed = array();

	/** @var array */
	protected $_isAllowedInAdmin = array();

	/**
	 * @var ExtendedRouteEntity
	 */
	protected $extendedPage;

	/**
	 * @var callable
	 */
	private $extendedPageCallback;


	/**
	 * @param ExtendedPageEntity $page
	 */
	public function __construct(ExtendedPageEntity $page)
	{
		parent::__construct();

		$this->children = new ArrayCollection;
		$this->routes = new ArrayCollection;
		$this->permissions = new ArrayCollection;
		$this->adminPermissions = new ArrayCollection;
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
	 * @param callable $extendedPageCallback
	 */
	public function setExtendedPageCallback($extendedPageCallback)
	{
		$this->extendedPageCallback = $extendedPageCallback;
	}


	/**
	 * @return \CmsModule\Content\Entities\ExtendedPageEntity
	 */
	public function getExtendedPage()
	{
		if (!$this->extendedPage) {
			$this->extendedPage = Callback::create($this->extendedPageCallback)->invoke();
		}

		return $this->extendedPage;
	}


	/**
	 * @ORM\PreRemove()
	 */
	public function onPreRemove()
	{
		$this->removeFromPosition();
	}


	/**
	 * @return PageEntity
	 */
	public function getParent()
	{
		return $this->parent;
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
	 * @return PageEntity
	 */
	public function getPrevious()
	{
		return $this->previous;
	}


	public function setNext(PageEntity $next = NULL, $recursively = TRUE)
	{
		if ($next === $this) {
			throw new InvalidArgumentException("Next page is the same as current page.");
		}

		$this->next = $next;

		if ($recursively && $next) {
			$next->setPrevious($this, FALSE);
		}
	}


	public function setPrevious(PageEntity $previous = NULL, $recursively = TRUE)
	{
		if ($previous === $this) {
			throw new InvalidArgumentException("Previous page is the same as current page.");
		}

		$this->previous = $previous;

		if ($recursively && $previous) {
			$previous->setNext($this, FALSE);
		}
	}


	/**
	 * @return PageEntity
	 */
	public function getNext()
	{
		return $this->next;
	}


	public function generatePosition($recursively = TRUE)
	{
		$position = $this->getPrevious() ? $this->getPrevious()->position + 1 : 1;

		$this->position = $position;

		if ($recursively && $this->getNext()) {
			$this->getNext()->generatePosition();
		}
	}


	public function getPosition()
	{
		return $this->position;
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
	 * @param PageEntity $parent
	 * @param null $setPrevious
	 * @param PageEntity $previous
	 */
	public function setParent(PageEntity $parent = NULL, $setPrevious = NULL, PageEntity $previous = NULL)
	{
		if ($parent == $this->getParent() && !$setPrevious) {
			return;
		}

		if (!$parent && !$this->getNext() && !$this->getPrevious() && !$this->getParent() && !$setPrevious) {
			return;
		}

		if ($setPrevious && $previous === $this) {
			throw new InvalidArgumentException("Previous page is the same as current page.");
		}

		$oldParent = $this->getParent();
		$oldPrevious = $this->getPrevious();
		$oldNext = $this->getNext();

		$this->removeFromPosition();

		if ($parent) {
			$this->parent = $parent;

			if ($setPrevious) {
				if ($previous) {
					$this->setNext($previous->next);
					$this->setPrevious($previous);
				} else {
					$this->setNext($parent->getChildren()->first() ? : NULL);
				}
			} else {
				$this->setPrevious($parent->getChildren()->last() ? : NULL);
			}

			$parent->children[] = $this;
		} else {
			if ($setPrevious) {
				if ($previous) {
					$this->setNext($previous->next);
					$this->setPrevious($previous);
				} else {
					$this->setNext($this->getRoot($oldNext ? : ($oldParent ? : ($oldPrevious))));
				}
			} else {
				$this->parent = NULL;
				$this->previous = NULL;
				$this->next = NULL;
			}
		}

		$this->getMainRoute()->parent = $this->getParent() && $this->getParent()->getMainRoute() ? $this->getParent()->getMainRoute() : NULL;
		$this->generatePosition();
		$this->generateUrl();
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
	 * @param $published
	 * @return $this
	 */
	public function setPublished($published)
	{
		$this->published = $published;
		return $this;
	}


	/**
	 * @return boolean
	 */
	public function getPublished()
	{
		return $this->published;
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
	 * @param boolean $adminSecured
	 */
	public function setAdminSecured($adminSecured)
	{
		$this->adminSecured = $adminSecured;
	}


	/**
	 * @return boolean
	 */
	public function getAdminSecured()
	{
		return $this->adminSecured;
	}


	/**
	 * @param PermissionEntity[]|ArrayCollection $permissions
	 */
	public function setPermissions($permissions)
	{
		$this->permissions = $permissions;
		$this->_isAllowed = array();
	}


	/**
	 * @return PermissionEntity[]|ArrayCollection
	 */
	public function getPermissions()
	{
		return $this->permissions;
	}


	/**
	 * @param AdminPermissionEntity[]|ArrayCollection $adminPermissions
	 */
	public function setAdminPermissions($adminPermissions)
	{
		$this->adminPermissions = $adminPermissions;
	}


	/**
	 * @return AdminPermissionEntity[]|ArrayCollection
	 */
	public function getAdminPermissions()
	{
		return $this->adminPermissions;
	}


	/**
	 * @param User $user
	 * @param $permission
	 * @return bool
	 */
	private function baseIsAllowed(& $secured, & $source, & $cache, User $user, $permission)
	{
		if (!$secured) {
			return TRUE;
		}

		if (!isset($cache[$user->id][$permission])) {

			if (!isset($cache[$user->id])) {
				$cache[$user->id] = array();
			}

			if ($user->isInRole('admin')) {
				$cache[$user->id][$permission] = TRUE;
				return TRUE;

			}

			if (isset($source[$permission])) {
				$permissionEntity = $source[$permission];

				if (!$user->isLoggedIn()) {
					$cache[$user->id][$permission] = FALSE;
					return FALSE;
				}

				if ($permissionEntity->getAll()) {
					$cache[$user->id][$permission] = TRUE;
					return TRUE;
				}

				foreach ($user->getRoles() as $role) {
					if (isset($permissionEntity->roles[$role])) {
						$cache[$user->id][$permission] = TRUE;
						return TRUE;
					}
				}
			}
			$cache[$user->id][$permission] = FALSE;
		}

		return $cache[$user->id][$permission];
	}


	/**
	 * @param User $user
	 * @param $permission
	 * @return bool
	 */
	public function isAllowed(User $user, $permission)
	{
		return $this->baseIsAllowed($this->secured, $this->permissions, $this->_isAllowed, $user, $permission);
	}


	/**
	 * @param User $user
	 * @param $permission
	 * @return bool
	 */
	public function isAllowedInBackend(User $user, $permission)
	{
		return $this->baseIsAllowed($this->adminSecured, $this->adminPermissions, $this->_isAllowedInAdmin, $user, $permission);
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


	/**
	 * @param PageEntity $entity
	 * @return PageEntity
	 */
	public function getRoot(PageEntity $entity = NULL)
	{
		$entity = $entity ? : $this;

		while ($entity->getParent()) {
			$entity = $entity->parent;
		}

		while ($entity->getPrevious()) {
			$entity = $entity->previous;
		}

		return $entity;
	}


	public function removeFromPosition()
	{
		if (!$this->getPrevious() && !$this->getNext() && !$this->getParent()) {
			return;
		}

		if ($this->getParent()) {
			foreach ($this->getParent()->getChildren() as $key => $item) {
				if ($item->id === $this->id) {
					$this->getParent()->children->remove($key);
					break;
				}
			}
		}

		if ($this->getMainRoute()->getParent()) {
			foreach ($this->mainRoute->parent->getChildren() as $key => $route) {
				if ($route->id === $this->mainRoute->id) {
					$this->mainRoute->parent->getChildren()->remove($key);
				}
			}
		}

		$next = $this->getNext();
		$previous = $this->getPrevious();

		if ($next) {
			$next->setPrevious($previous, FALSE);
		}

		if ($previous) {
			$previous->setNext($next, FALSE);
		}

		if ($next) {
			$next->generatePosition();
		}

		$this->setPrevious(NULL);
		$this->parent = NULL;
		$this->setNext(NULL);
	}


	/**
	 * Generate URL.
	 */
	private function generateUrl($recursively = TRUE)
	{
		foreach ($this->routes as $route) {
			$route->generateUrl($recursively);
		}
	}
}
