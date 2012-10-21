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
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 * @Entity(repositoryClass="\CmsModule\Content\Repositories\PageRepository")
 * @Table(name="page")
 * @InheritanceType("JOINED")
 * @DiscriminatorColumn(name="type", type="string")
 * @DiscriminatorMap({"base" = "PageEntity"})
 */
abstract class PageEntity extends TreeEntity
{

	const CACHE = 'Cms.PageEntity';

	/**
	 * @var string
	 * @Column(type="string")
	 */
	protected $name;

	/**
	 * @var ArrayCollection|RouteEntity[]
	 * @OneToMany(targetEntity="\CmsModule\Content\Entities\RouteEntity", mappedBy="page", cascade={"persist", "remove", "detach"})
	 */
	protected $routes;

	/**
	 * @var RouteEntity
	 * @ManyToOne(targetEntity="\CmsModule\Content\Entities\RouteEntity", cascade={"persist", "remove", "detach"})
	 * @JoinColumn(name="route_id", referencedColumnName="id", onDelete="CASCADE")
	 */
	protected $mainRoute;

	/**
	 * @var ArrayCollection|LanguageEntity[]
	 * @ManyToMany(targetEntity="\CmsModule\Content\Entities\LanguageEntity", inversedBy="pages")
	 * @JoinTable(name="pageLanguageLink",
	 *       joinColumns={@JoinColumn(name="page_id", referencedColumnName="id", onDelete="CASCADE")},
	 *       inverseJoinColumns={@JoinColumn(name="language_id", referencedColumnName="id", onDelete="CASCADE")}
	 *       )
	 */
	protected $languages;

	/**
	 * @var PageEntity
	 * @ManyToOne(targetEntity="\CmsModule\Content\Entities\PageEntity", inversedBy="translations")
	 * @JoinColumn(name="translationFor", referencedColumnName="id", onDelete="CASCADE")
	 */
	protected $translationFor;

	/**
	 * @var ArrayCollection|PageEntity[]
	 * @OneToMany(targetEntity="\CmsModule\Content\Entities\PageEntity", mappedBy="translationFor")
	 */
	protected $translations;

	/**
	 * @var \DateTime
	 * @Column(type="datetime")
	 */
	protected $created;

	/**
	 * @var \DateTime
	 * @Column(type="datetime")
	 */
	protected $updated;

	/**
	 * @var \DateTime
	 * @Column(type="datetime", nullable=true)
	 */
	protected $expired;


	/**
	 * @param $type
	 */
	public function __construct()
	{
		parent::__construct();

		$this->name = "";
		$this->languages = new ArrayCollection;
		$this->translations = new ArrayCollection;
		$this->routes = new ArrayCollection;
		$this->created = new \Nette\DateTime;
		$this->updated = new \Nette\DateTime;

		$this->mainRoute = new RouteEntity;
		$this->routes[] = $this->mainRoute;
		$this->mainRoute->page = $this;
	}


	/**
	 * @return string
	 */
	public function __toString()
	{
		return $this->name;
	}


	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}


	/**
	 * @param $name
	 */
	public function setName($name)
	{
		if ($this->name === $name) {
			return;
		}

		if ($this->mainRoute->getTitle() === $this->name) {
			$this->mainRoute->setTitle($name);
		} else if (!$this->mainRoute->getTitle()) {
			$this->mainRoute->setTitle($name);
		}

		$this->name = $name;
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
	 * Generate URL.
	 */
	protected function generateUrl()
	{
		foreach ($this->routes as $route) {
			$route->generateUrl();
		}
	}


	/**
	 * @return ArrayCollection|LanguageEntity[]
	 */
	public function getLanguages()
	{
		return $this->languages;
	}


	/**
	 * @param $languages
	 */
	public function setLanguages($languages)
	{
		$this->languages = $languages;
	}


	/**
	 * @return PageEntity|NULL
	 */
	public function getTranslationFor()
	{
		return $this->translationFor;
	}


	/**
	 * @param $translationFor
	 */
	public function setTranslationFor($translationFor)
	{
		$this->parent = NULL;
		$this->previous = NULL;
		$this->translationFor = $translationFor;
	}


	/**
	 * @return ArrayCollection|PageEntity[]
	 */
	public function getTranslations()
	{
		return $this->translations;
	}


	/**
	 * @param $translations
	 */
	public function setTranslations($translations)
	{
		$this->translations = $translations;
	}


	/**
	 * Check if page is in language alias.
	 *
	 * @param string $alias
	 * @return bool
	 */
	public function isInLanguageAlias($alias)
	{
		foreach ($this->languages as $language) {
			if ($language->alias == $alias) {
				return true;
			}
		}
		return false;
	}


	/**
	 * Return the same page in other language alias.
	 *
	 * @param string $alias
	 * @return \CmsModule\Content\Entities\PageEntity
	 */
	public function getPageWithLanguageAlias($alias)
	{
		if ($this->isInLanguageAlias($alias)) {
			return $this;
		}

		if (!$this->translationFor) {
			foreach ($this->translations as $page) {
				if ($page->isInLanguageAlias($alias)) {
					return $page;
				}
			}
		} else {
			if ($this->translationFor->isInLanguageAlias($alias)) {
				return $this->translationFor;
			}

			foreach ($this->translationFor->translations as $page) {
				if ($page === $this) {
					continue;
				}

				if ($page->isInLanguageAlias($alias)) {
					return $page;
				}
			}
		}
		return NULL;
	}


	/**
	 * @param $childrens
	 */
	public function setChildrens($childrens)
	{
		$this->childrens = $childrens;
	}


	/**
	 * @return ArrayCollection|PageEntity[]
	 */
	public function getChildrens()
	{
		return $this->childrens;
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
	 * @param $order
	 */
	public function setOrder($order)
	{
		$this->order = $order;
	}


	/**
	 * @return int
	 */
	public function getOrder()
	{
		return $this->order;
	}


	/**
	 * @return string
	 */
	public static function getType()
	{
		$ref = new \Nette\Reflection\ClassType(get_called_class());
		$annotation = $ref->getAnnotation('DiscriminatorEntry');
		return $annotation['name'];
	}
}
