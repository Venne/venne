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

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 * @ORM\Entity
 * @ORM\Table(name="route_translation", indexes={
 * @ORM\Index(name="url_idx", columns={"url"}),
 * })
 */
class RouteTranslationEntity extends \DoctrineModule\Entities\IdentifiedEntity
{

	const CACHE = 'Cms.RouteTranslationEntity';

	/**
	 * @var RouteEntity
	 * @ORM\ManyToOne(targetEntity="\CmsModule\Content\Entities\RouteEntity", inversedBy="translations")
	 * @ORM\JoinColumn(name="object_id", referencedColumnName="id", onDelete="CASCADE")
	 */
	protected $object;

	/**
	 * @var LanguageEntity
	 * @ORM\ManyToOne(targetEntity="\CmsModule\Content\Entities\LanguageEntity")
	 * @ORM\JoinColumn(name="language", referencedColumnName="id", onDelete="CASCADE")
	 */
	protected $language;

	/**
	 * @ORM\Column(type="string", nullable=true)
	 */
	protected $url;

	/**
	 * @ORM\Column(type="string", nullable=true)
	 */
	protected $localUrl;

	/**
	 * @ORM\Column(type="string", nullable=true)
	 */
	protected $name;

	/**
	 * @ORM\Column(type="text", nullable=true)
	 */
	protected $notation;

	/**
	 * @ORM\Column(type="string", nullable=true)
	 */
	protected $title;

	/**
	 * @ORM\Column(type="string", nullable=true)
	 */
	protected $keywords;

	/**
	 * @ORM\Column(type="string", nullable=true)
	 */
	protected $description;

	/**
	 * @ORM\Column(type="text", nullable=true)
	 */
	protected $text;


	/**
	 * @param RouteEntity $object
	 * @param LanguageEntity $language
	 */
	public function __construct(RouteEntity $object, LanguageEntity $language)
	{
		$this->object = $object;
		$this->language = $language;
	}


	/**
	 * @param mixed $description
	 */
	public function setDescription($description)
	{
		$this->description = $description;
	}


	/**
	 * @return mixed
	 */
	public function getDescription()
	{
		return $this->description;
	}


	/**
	 * @param mixed $keywords
	 */
	public function setKeywords($keywords)
	{
		$this->keywords = $keywords;
	}


	/**
	 * @return mixed
	 */
	public function getKeywords()
	{
		return $this->keywords;
	}


	/**
	 * @param \CmsModule\Content\Entities\LanguageEntity $language
	 */
	public function setLanguage($language)
	{
		$this->language = $language;
	}


	/**
	 * @return \CmsModule\Content\Entities\LanguageEntity
	 */
	public function getLanguage()
	{
		return $this->language;
	}


	/**
	 * @param mixed $localUrl
	 */
	public function setLocalUrl($localUrl)
	{
		$this->localUrl = $localUrl;
	}


	/**
	 * @return mixed
	 */
	public function getLocalUrl()
	{
		return $this->localUrl;
	}


	/**
	 * @param mixed $name
	 */
	public function setName($name)
	{
		$this->name = $name;
	}


	/**
	 * @return mixed
	 */
	public function getName()
	{
		return $this->name;
	}


	/**
	 * @param mixed $notation
	 */
	public function setNotation($notation)
	{
		$this->notation = $notation;
	}


	/**
	 * @return mixed
	 */
	public function getNotation()
	{
		return $this->notation;
	}


	/**
	 * @param \CmsModule\Content\Entities\RouteEntity $object
	 */
	public function setObject($object)
	{
		$this->object = $object;
	}


	/**
	 * @return \CmsModule\Content\Entities\RouteEntity
	 */
	public function getObject()
	{
		return $this->object;
	}


	/**
	 * @param mixed $title
	 */
	public function setTitle($title)
	{
		$this->title = $title;
	}


	/**
	 * @return mixed
	 */
	public function getTitle()
	{
		return $this->title;
	}


	/**
	 * @param mixed $url
	 */
	public function setUrl($url)
	{
		$this->url = $url;
	}


	/**
	 * @return mixed
	 */
	public function getUrl()
	{
		return $this->url;
	}


	/**
	 * @param mixed $text
	 */
	public function setText($text)
	{
		$this->text = $text;
	}


	/**
	 * @return mixed
	 */
	public function getText()
	{
		return $this->text;
	}
}
