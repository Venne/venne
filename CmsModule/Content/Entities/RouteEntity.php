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

use CmsModule\Pages\Tags\TagEntity;
use CmsModule\Pages\Users\UserEntity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Nette\Callback;
use Nette\InvalidArgumentException;
use Nette\Utils\Strings;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 * @ORM\Entity(repositoryClass="\CmsModule\Content\Repositories\RouteRepository")
 * @ORM\EntityListeners({"\CmsModule\Content\Listeners\ExtendedRouteListener"})
 * @ORM\Table(name="route", indexes={
 * @ORM\Index(name="type_idx", columns={"type"}),
 * @ORM\Index(name="url_idx", columns={"url"}),
 * @ORM\Index(name="expired_idx", columns={"expired"}),
 * @ORM\Index(name="released_idx", columns={"released"}),
 * })
 *
 * @property string $type
 * @property array $params
 */
class RouteEntity extends \DoctrineModule\Entities\IdentifiedEntity
{

	const CACHE = 'Cms.RouteEntity';

	const DEFAULT_CACHE_MODE = 'default';

	const CACHE_MODE_TIME = 'time';

	const CACHE_MODE_STATIC = 'static';

	/** @var array */
	protected static $robotsValues = array(
		'index, follow',
		'noindex, follow',
		'index, nofollow',
		'noindex, nofollow',
	);

	/** @var array */
	protected static $changefreqValues = array(
		'always',
		'hourly',
		'daily',
		'weekly',
		'monthly',
		'yearly',
		'never',
	);

	/** @var array */
	protected static $cacheModes = array(
		self::CACHE_MODE_TIME,
		self::CACHE_MODE_STATIC,
	);

	/** @var array */
	protected static $priorityValues = array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10);

	/**
	 * @ORM\Column(type="string")
	 */
	protected $type = '';

	/**
	 * @ORM\Column(type="string")
	 */
	protected $url = '';

	/**
	 * @ORM\Column(type="string")
	 */
	protected $localUrl = '';

	/** @ORM\Column(type="string") */
	protected $params = '[]';

	/** @ORM\Column(type="integer") */
	protected $paramCounter = 0;

	/**
	 * @var PageEntity
	 * @ORM\ManyToOne(targetEntity="\CmsModule\Content\Entities\PageEntity", inversedBy="routes")
	 * @ORM\JoinColumn(name="page_id", referencedColumnName="id", onDelete="CASCADE")
	 */
	protected $page;

	/**
	 * @var LanguageEntity
	 * @ORM\ManyToOne(targetEntity="\CmsModule\Content\Entities\LanguageEntity")
	 */
	protected $language;

	/**
	 * @var RouteEntity
	 * @ORM\ManyToOne(targetEntity="\CmsModule\Content\Entities\RouteEntity", inversedBy="children")
	 * @ORM\JoinColumn(name="route_id", referencedColumnName="id", onDelete="CASCADE")
	 */
	protected $parent;

	/**
	 * @var RouteEntity[]
	 * @ORM\OneToMany(targetEntity="\CmsModule\Content\Entities\RouteEntity", mappedBy="parent", fetch="EXTRA_LAZY")
	 */
	protected $children;

	/**
	 * @var LayoutEntity
	 * @ORM\ManyToOne(targetEntity="\CmsModule\Content\Entities\LayoutEntity", inversedBy="routes", cascade={"persist"})
	 * @ORM\JoinColumn(onDelete="SET NULL")
	 */
	protected $layout;

	/**
	 * @var LayoutEntity
	 * @ORM\ManyToOne(targetEntity="\CmsModule\Content\Entities\LayoutEntity", cascade={"persist"})
	 * @ORM\JoinColumn(onDelete="SET NULL")
	 */
	protected $childrenLayout;

	/**
	 * @var bool
	 * @ORM\Column(type="boolean")
	 */
	protected $published = FALSE;

	/**
	 * @var TagEntity[]
	 * @ORM\ManyToMany(targetEntity="\CmsModule\Pages\Tags\TagEntity", inversedBy="routes", cascade={"all"})
	 * @ORM\JoinTable(name="routes_tags")
	 **/
	protected $tags;

	/**
	 * @var FileEntity
	 * @ORM\OneToOne(targetEntity="\CmsModule\Content\Entities\FileEntity", cascade={"all"}, orphanRemoval=true)
	 * @ORM\JoinColumn(onDelete="SET NULL")
	 */
	protected $photo;

	/**
	 * @ORM\Column(type="text")
	 */
	protected $text = '';

	/**
	 * @var \DateTime
	 * @ORM\Column(type="datetime")
	 */
	protected $created;

	/**
	 * @var \DateTime
	 * @ORM\Column(type="datetime", nullable=true)
	 */
	protected $updated;

	/**
	 * @var \DateTime
	 * @ORM\Column(type="datetime", nullable=true)
	 */
	protected $expired;

	/**
	 * @var \DateTime
	 * @ORM\Column(type="datetime")
	 */
	protected $released;

	/***************** Meta *******************/

	/**
	 * @ORM\Column(type="string")
	 */
	protected $name = '';

	/**
	 * @ORM\Column(type="text")
	 */
	protected $notation = '';

	/**
	 * @ORM\Column(type="string")
	 */
	protected $title = '';

	/**
	 * @ORM\Column(type="string")
	 */
	protected $keywords = '';

	/**
	 * @ORM\Column(type="string")
	 */
	protected $description = '';

	/**
	 * @var \CmsModule\Pages\Users\UserEntity
	 * @ORM\ManyToOne(targetEntity="\CmsModule\Pages\Users\UserEntity", inversedBy="routes")
	 * @ORM\JoinColumn(onDelete="CASCADE")
	 */
	protected $author;

	/** @ORM\Column(type="string") */
	protected $robots = '';

	/** @ORM\Column(type="string", nullable=true) */
	protected $changefreq;

	/** @ORM\Column(type="integer", nullable=true) */
	protected $priority;

	/** @ORM\Column(type="boolean") */
	protected $copyLayoutFromParent = TRUE;

	/** @ORM\Column(type="string", nullable=true) */
	protected $cacheMode;

	/** @ORM\Column(type="boolean") */
	protected $copyCacheModeFromParent = TRUE;

	/** @ORM\Column(type="boolean") */
	protected $copyLayoutToChildren = TRUE;

	/**
	 * @var RouteTranslationEntity[]
	 * @ORM\OneToMany(targetEntity="\CmsModule\Content\Entities\RouteTranslationEntity", mappedBy="object", indexBy="language", cascade={"persist"}, fetch="EXTRA_LAZY")
	 */
	protected $translations;

	/**
	 * @var LanguageEntity
	 */
	protected $locale;

	/**
	 * @var string
	 * @ORM\Column(type="string")
	 */
	protected $class;

	/**
	 * @var DirEntity
	 * @ORM\OneToOne(targetEntity="\CmsModule\Content\Entities\DirEntity", cascade={"all"})
	 * @ORM\JoinColumn(onDelete="SET NULL")
	 */
	protected $dir;

	/**
	 * @var ExtendedRouteEntity
	 */
	protected $extendedRoute;

	/**
	 * @var callable
	 */
	private $extendedRouteCallback;


	/**
	 * @return string
	 */
	public function __toString()
	{
		return $this->getTranslatedValue('url');
	}


	/**
	 * @param PageEntity $page
	 * @param $type
	 */
	public function __construct(PageEntity $page, $type)
	{
		$this->page = $page;
		$this->language = $page->language;
		$this->setParent($page->mainRoute);
		$this->class = $type;
		$this->children = new ArrayCollection;
		$this->tags = new ArrayCollection;
		$this->translations = new ArrayCollection;
		$this->cacheMode = self::DEFAULT_CACHE_MODE;
		$this->created = new \DateTime;
		$this->released = new \DateTime;
	}


	/**
	 * @param callable $extendedRouteCallback
	 */
	public function setExtendedRouteCallback($extendedRouteCallback)
	{
		$this->extendedRouteCallback = $extendedRouteCallback;
	}


	/**
	 * @return \CmsModule\Content\Entities\ExtendedRouteEntity
	 */
	public function getExtendedRoute()
	{
		if (!$this->extendedRoute) {
			$this->extendedRoute = Callback::create($this->extendedRouteCallback)->invoke();
		}

		return $this->extendedRoute;
	}


	/**
	 * @return string
	 */
	public function getUrl()
	{
		return $this->getTranslatedValue('url');
	}


	/**
	 * @return string
	 */
	public function getLocalUrl()
	{
		return $this->getTranslatedValue('localUrl');
	}


	public function generateDate()
	{
		$this->updated = new \DateTime;
	}


	/**
	 * @param bool $recursively
	 */
	public function generateUrl($recursively = TRUE)
	{
		if (!$this->getParent()) {
			$this->setTranslatedValue('url', '');
		} else {
			$l = $this->getParent()->getLocale();
			$this->getParent()->setLocale($this->locale);
			$this->setTranslatedValue('url', trim($this->getParent()->getUrl() . '/' . $this->getTranslatedValue('localUrl'), '/'));
			$this->getParent()->setLocale($l);
		}

		if ($recursively) {
			foreach ($this->getChildren() as $children) {
				$children->generateUrl();
			}
		}
	}


	/**
	 * @param bool $recursively
	 */
	public function generateLayouts($recursively = TRUE)
	{
		if ($this->getCopyLayoutFromParent()) {
			$this->layout = $this->getParent() ? ($this->parent->copyLayoutToChildren ? $this->parent->layout : $this->parent->childrenLayout) : NULL;
		}

		if ($this->getCopyLayoutToChildren()) {
			$this->childrenLayout = $this->layout;
		}

		if ($this->getCopyCacheModeFromParent()) {
			$this->cacheMode = $this->getParent() ? $this->parent->cacheMode : self::DEFAULT_CACHE_MODE;
		}

		if ($recursively) {
			foreach ($this->getChildren() as $children) {
				$children->generateLayouts();
			}
		}
	}


	/**
	 * @param $localUrl
	 * @param bool $recursively
	 * @return $this
	 */
	public function setLocalUrl($localUrl, $recursively = TRUE)
	{
		$this->setTranslatedValue('localUrl', $localUrl);
		$this->generateUrl($recursively);
		return $this;
	}


	/**
	 * @return array
	 */
	public function getParams()
	{
		return (array)json_decode($this->params);
	}


	/**
	 * @param $params
	 */
	public function setParams($params)
	{
		$delete = array('module', 'presenter', 'action');
		foreach ($delete as $item) {
			if (isset($params[$item])) {
				unset($params[$item]);
			}
		}

		ksort($params);
		$this->params = json_encode($params);
		$this->paramCounter = count($params);
	}


	/**
	 * @param $text
	 * @return $this
	 */
	public function setText($text)
	{
		if ($this->text == $text) {
			return;
		}

		$this->setTranslatedValue('text', $text);
		$this->generateDate();
		return $this;
	}


	/**
	 * @return mixed
	 */
	public function getText()
	{
		return $this->getTranslatedValue('text');
	}


	/**
	 * @return \DateTime
	 */
	public function getCreated()
	{
		return $this->created;
	}


	/**
	 * @return \DateTime
	 */
	public function getUpdated()
	{
		return $this->updated;
	}


	/**
	 * @param \DateTime $expired
	 */
	public function setExpired($expired)
	{
		$this->expired = $expired;
	}


	/**
	 * @return \DateTime
	 */
	public function getExpired()
	{
		return $this->expired;
	}


	/**
	 * @param \DateTime $released
	 */
	public function setReleased($released)
	{
		$this->released = $released;
	}


	/**
	 * @return \DateTime
	 */
	public function getReleased()
	{
		return $this->released;
	}


	/**
	 * @return mixed
	 */
	public function getType()
	{
		return $this->type;
	}


	/**
	 * @param $type
	 */
	public function setType($type)
	{
		$this->type = $type;
	}


	/**
	 * @return mixed
	 */
	public function getParent()
	{
		return $this->parent;
	}


	/**
	 * @param RouteEntity $parent
	 */
	public function setParent(RouteEntity $parent = NULL)
	{
		if ($this->getParent() == $parent) {
			return;
		}

		$this->parent = $parent;
		if ($parent) {
			$parent->children[] = $this;
		}

		$this->generateUrl();
		$this->generateLayouts();
	}


	/**
	 * @param $children
	 */
	public function setChildren($children)
	{
		$this->children = $children;
	}


	/**
	 * @return ArrayCollection
	 */
	public function getChildren()
	{
		if ($this->children === NULL) {
			$this->children = new ArrayCollection;
		}
		return $this->children;
	}


	/**
	 * @param LanguageEntity $language
	 */
	public function setLanguage($language)
	{
		$this->language = $language;
	}


	/**
	 * @return LanguageEntity
	 */
	public function getLanguage()
	{
		return $this->language;
	}


	public function getPage()
	{
		return $this->page;
	}


	public function setAuthor(UserEntity $author = NULL)
	{
		$this->author = $author;
	}


	public function getAuthor()
	{
		return $this->author;
	}


	/**
	 * @param $name
	 * @return $this
	 */
	public function setName($name)
	{
		$this->setTranslatedValue('name', $name);
		return $this;
	}


	/**
	 * @return mixed
	 */
	public function getName()
	{
		return $this->getTranslatedValue('name');
	}


	/**
	 * @param mixed $notation
	 */
	public function setNotation($notation)
	{
		$this->setTranslatedValue('notation', $notation);
	}


	/**
	 * @return mixed
	 */
	public function getNotation()
	{
		return $this->getTranslatedValue('notation');
	}


	public function setKeywords($keywords)
	{
		$this->setTranslatedValue('keywords', $keywords);
	}


	public function getKeywords()
	{
		return $this->getTranslatedValue('keywords');
	}


	public function setRobots($robots)
	{
		if (array_search($robots, self::$robotsValues) === FALSE) {
			throw new InvalidArgumentException("Variable must be one of [" . join(', ', self::$robotsValues) . "]. {$robots} is given.");
		}

		$this->robots = $robots;
	}


	public function getRobots()
	{
		return $this->robots;
	}


	/**
	 * @param $title
	 * @return $this
	 */
	public function setTitle($title)
	{
		$this->setTranslatedValue('title', $title);
		return $this;
	}


	public function getTitle()
	{
		return $this->getTranslatedValue('title');
	}


	/**
	 * @param LayoutEntity $layout
	 * @return $this
	 */
	public function setLayout(LayoutEntity $layout = NULL)
	{
		if ($layout === NULL && $this->layout === NULL) {
			return $this;
		}

		if ($layout && $this->layout && $layout->id == $this->layout->id) {
			return $this;
		}

		$this->layout = $layout;
		$this->generateLayouts();
		return $this;
	}


	public function getLayout()
	{
		return $this->layout;
	}


	/**
	 * @param LayoutEntity $childrenLayout
	 * @return $this
	 */
	public function setChildrenLayout(LayoutEntity $childrenLayout = NULL)
	{
		if ($childrenLayout === NULL && $this->childrenLayout === NULL) {
			return;
		}

		if ($childrenLayout && $this->childrenLayout && $childrenLayout->id == $this->childrenLayout->id) {
			return;
		}

		$this->childrenLayout = $childrenLayout;
		$this->generateLayouts();
		return $this;
	}


	public function getChildrenLayout()
	{
		return $this->childrenLayout;
	}


	/**
	 * @param $description
	 * @return $this
	 */
	public function setDescription($description)
	{
		$this->description = $description;
		return $this;
	}


	public function getDescription()
	{
		return $this->description;
	}


	/**
	 * @param $copyLayoutFromParent
	 * @return $this
	 */
	public function setCopyLayoutFromParent($copyLayoutFromParent)
	{
		if ($this->copyLayoutFromParent == $copyLayoutFromParent) {
			return;
		}

		$this->copyLayoutFromParent = (bool)$copyLayoutFromParent;
		$this->generateLayouts();
		return $this;
	}


	public function getCopyLayoutFromParent()
	{
		return $this->copyLayoutFromParent;
	}


	public function setCopyLayoutToChildren($copyLayoutToChildren)
	{
		if ($this->copyLayoutToChildren == $copyLayoutToChildren) {
			return;
		}

		$this->copyLayoutToChildren = $copyLayoutToChildren;
		$this->generateLayouts();
	}


	public function getCopyLayoutToChildren()
	{
		return $this->copyLayoutToChildren;
	}


	public function setChangefreq($changefreq)
	{
		if ($changefreq !== NULL && array_search($changefreq, self::$changefreqValues) === FALSE) {
			throw new InvalidArgumentException("Variable must be one of [" . join(', ', self::$changefreqValues) . "]. {$changefreq} is given.");
		}

		$this->changefreq = $changefreq;
	}


	public function getChangefreq()
	{
		return $this->changefreq;
	}


	public function setPriority($priority)
	{
		$priority = (int)$priority;

		if (!is_integer($priority) || $priority < 0 || $priority > 10) {
			throw new InvalidArgumentException("Priority must be between 0 and 10");
		}

		$this->priority = $priority;
	}


	public function getPriority()
	{
		return $this->priority;
	}


	public function setCopyCacheModeFromParent($copyCacheModeFromParent)
	{
		$this->copyCacheModeFromParent = $copyCacheModeFromParent;
	}


	public function getCopyCacheModeFromParent()
	{
		return $this->copyCacheModeFromParent;
	}


	public function setCacheMode($cacheMode)
	{
		if ($this->cacheMode == $cacheMode || $this->copyCacheModeFromParent) {
			return;
		}

		$this->cacheMode = $cacheMode;
		$this->generateLayouts();
	}


	public function getCacheMode()
	{
		return $this->cacheMode;
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
	 * @param mixed $tags
	 */
	public function setTags($tags)
	{
		$this->tags = $tags;
	}


	/**
	 * @return mixed
	 */
	public function getTags()
	{
		return $this->tags;
	}


	/**
	 * @return string
	 */
	public function getClass()
	{
		return $this->class;
	}


	/**
	 * @param \CmsModule\Content\Entities\FileEntity $photo
	 */
	public function setPhoto($photo)
	{
		$this->photo = $photo;

		if ($this->photo) {
			$this->photo->setParent($this->getDir());
			$this->photo->setInvisible(TRUE);
		}
	}


	/**
	 * @return \CmsModule\Content\Entities\FileEntity
	 */
	public function getPhoto()
	{
		return $this->photo;
	}


	/**
	 * @param RouteTranslationEntity[] $translations
	 */
	public function setTranslations($translations)
	{
		$this->translations = $translations;
	}


	/**
	 * @return RouteTranslationEntity[]
	 */
	public function getTranslations()
	{
		return $this->translations;
	}


	/**
	 * @param LanguageEntity $locale
	 */
	public function setLocale(LanguageEntity $locale = NULL)
	{
		$this->locale = $locale;
	}


	/**
	 * @return LanguageEntity
	 */
	public function getLocale()
	{
		return $this->locale;
	}


	/**
	 * @return DirEntity
	 */
	public function getDir()
	{
		if (!$this->dir) {
			$this->dir = new DirEntity;
			$this->dir->setParent($this->page->getDir());
			$this->dir->setInvisible(TRUE);
			$this->dir->setName(Strings::webalize(get_class($this)) . Strings::random());
		}

		return $this->dir;
	}


	public static function getChangefreqValues()
	{
		return self::$changefreqValues;
	}


	public static function getPriorityValues()
	{
		return self::$priorityValues;
	}


	public static function getRobotsValues()
	{
		return self::$robotsValues;
	}


	public static function getCacheModes()
	{
		return self::$cacheModes;
	}


	/**
	 * @param $field
	 * @param LanguageEntity $language
	 * @return mixed
	 */
	protected function getTranslatedValue($field, LanguageEntity $language = NULL)
	{
		$language = $language ? : $this->locale;

		if ($language && $this->translations[$language->id]) {
			if (($ret = $this->translations[$language->id]->{$field}) !== NULL) {
				return $ret;
			}
		}

		return $this->{$field};
	}


	/**
	 * @param $field
	 * @param $value
	 * @param LanguageEntity $language
	 */
	protected function setTranslatedValue($field, $value, LanguageEntity $language = NULL)
	{
		$language = $language ? : $this->locale;

		if ($language) {
			if (!isset($this->translations[$language->id])) {
				if ($value === NULL || $this->{$field} === $value) {
					return;
				}

				$this->translations[$language->id] = new RouteTranslationEntity($this, $language);
			}
			$this->translations[$language->id]->{$field} = $value ? : NULL;
		} else {
			$this->{$field} = $value;
		}
	}


	/**
	 * @param $name
	 * @param $value
	 * @throws \RuntimeException
	 */
	public function setValueForAllTranslations($name, $value)
	{
		$method = 'set' . ucfirst($name);

		$reflection = new \ReflectionMethod($this, $method);
		if (!$reflection->isPublic()) {
			throw new \RuntimeException("The called method is not public.");
		}

		$locale = $this->locale;
		$this->locale = NULL;
		call_user_func(array($this, $method), $value);
		foreach ($this->translations as $translation) {
			$this->locale = $translation->getLanguage();
			call_user_func(array($this, $method), $value);
		}
		$this->locale = $locale;
	}
}
