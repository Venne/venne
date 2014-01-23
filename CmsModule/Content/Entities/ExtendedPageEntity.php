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
use DoctrineModule\Entities\IdentifiedEntity;
use Nette\InvalidArgumentException;
use Nette\Security\User;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 *
 * @ORM\MappedSuperclass
 *
 * @property-read PageEntity $page
 * @property-read ExtendedRouteEntity $extendedMainRoute
 * @property-read string $special
 * @property LanguageEntity $locale
 */
abstract class ExtendedPageEntity extends IdentifiedEntity
{

	const CACHE = 'Cms.ExtendedPageEntity';

	const PRIVILEGE_SHOW = 'show';

	const ADMIN_PRIVILEGE_SHOW = 'admin_show';

	const ADMIN_PRIVILEGE_PERMISSIONS = 'admin_permissions';

	const ADMIN_PRIVILEGE_ROUTES = 'admin_routes';

	const ADMIN_PRIVILEGE_PUBLICATION = 'admin_publication';

	const ADMIN_PRIVILEGE_PREVIEW = 'admin_preview';

	const ADMIN_PRIVILEGE_BASE = 'admin_base';

	const ADMIN_PRIVILEGE_REMOVE = 'admin_remove';

	const ADMIN_PRIVILEGE_CHANGE_STRUCTURE = 'admin_change_structure';

	/**
	 * @var PageEntity
	 * @ORM\OneToOne(targetEntity="\CmsModule\Content\Entities\PageEntity", cascade={"ALL"})
	 * @ORM\JoinColumn(name="page_id", referencedColumnName="id", onDelete="CASCADE")
	 */
	protected $page;

	/**
	 * @var ExtendedRouteEntity
	 * @ORM\OneToOne(targetEntity="::dynamic", cascade={"persist"})
	 * @ORM\JoinColumn(referencedColumnName="id", onDelete="CASCADE")
	 */
	protected $extendedMainRoute;

	/**
	 * @var LanguageEntity
	 */
	protected $locale;


	public function __construct()
	{
		$this->page = $this->createPageEntity();
		$this->extendedMainRoute = $this->createMainRoute();
		$this->page->mainRoute = $this->extendedMainRoute->getRoute();
		$this->page->special = $this->getSpecial();
		$this->startup();
	}


	public function __toString()
	{
		return (string)$this->getExtendedMainRoute();
	}


	protected function startup()
	{
	}


	/**
	 * @return PageEntity
	 */
	public function getPage()
	{
		return $this->page;
	}


	/**
	 * @return ExtendedRouteEntity
	 */
	public function getExtendedMainRoute()
	{
		return $this->extendedMainRoute;
	}


	/**
	 * @return ExtendedRouteEntity
	 */
	private function createMainRoute()
	{
		return $this->createRoute(static::getExtendedMainRouteName());
	}


	/**
	 * @param string $class
	 * @return ExtendedRouteEntity
	 */
	protected function createRoute($class)
	{
		$route = new $class($this);
		if (!$route instanceof ExtendedRouteEntity) {
			throw new InvalidArgumentException("Class '{$class}' is not instance of 'Cms\Content\Entities\ExtendedRouteEntity'.");
		}
		return $route;
	}


	/**
	 * @return PageEntity
	 */
	private function createPageEntity()
	{
		return new PageEntity($this);
	}


	public static function getExtendedMainRouteName()
	{
		return static::getReflection()->getNamespaceName() . '\RouteEntity';
	}


	/**
	 * @param LanguageEntity $locale
	 */
	public function setLocale(LanguageEntity $locale = NULL)
	{
		$this->page->locale = $this->locale = $locale;
	}


	/**
	 * @return LanguageEntity
	 */
	public function getLocale()
	{
		return $this->locale;
	}


	/**
	 * @return string
	 */
	protected function getSpecial()
	{
		return NULL;
	}


	/**
	 * @param User $user
	 * @param $permission
	 * @return bool
	 */
	public function isAllowed(User $user, $permission)
	{
		return $this->page->isAllowed($user, $permission);
	}


	/**
	 * @param User $user
	 * @param $permission
	 * @return bool
	 */
	public function isAllowedInBackend(User $user, $permission)
	{
		return $this->page->isAllowedInBackend($user, $permission);
	}


	/**
	 * @return array
	 */
	public function getPrivileges()
	{
		return array(
			self::PRIVILEGE_SHOW => 'show page',
		);
	}


	/**
	 * @return array
	 */
	public function getAdminPrivileges()
	{
		return array(
			self::ADMIN_PRIVILEGE_SHOW => 'show page',
			self::ADMIN_PRIVILEGE_PERMISSIONS => 'permissios',
			self::ADMIN_PRIVILEGE_PUBLICATION => 'publication',
			self::ADMIN_PRIVILEGE_PREVIEW => 'preview page',
			self::ADMIN_PRIVILEGE_ROUTES => 'edit routes',
			self::ADMIN_PRIVILEGE_BASE => 'edit base form',
			self::ADMIN_PRIVILEGE_REMOVE => 'remove page',
			self::ADMIN_PRIVILEGE_CHANGE_STRUCTURE => 'change page structure',
		);
	}
}
