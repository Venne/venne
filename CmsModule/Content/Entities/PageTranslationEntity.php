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
 * @ORM\Table(name="page_translation")
 */
class PageTranslationEntity extends \DoctrineModule\Entities\IdentifiedEntity
{

	const CACHE = 'Cms.PageTranslationEntity';

	/**
	 * @var PageEntity
	 * @ORM\ManyToOne(targetEntity="\CmsModule\Content\Entities\PageEntity", inversedBy="translations")
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
	 * @ORM\Column(type="text", nullable=true)
	 */
	protected $navigationTitle;


	/**
	 * @param PageEntity $object
	 * @param LanguageEntity $language
	 */
	public function __construct(PageEntity $object, LanguageEntity $language)
	{
		$this->object = $object;
		$this->language = $language;
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
	 * @return \CmsModule\Content\Entities\RouteEntity
	 */
	public function getObject()
	{
		return $this->object;
	}


	/**
	 * @param mixed $navigationTitle
	 */
	public function setNavigationTitle($navigationTitle)
	{
		$this->navigationTitle = $navigationTitle;
	}


	/**
	 * @return mixed
	 */
	public function getNavigationTitle()
	{
		return $this->navigationTitle;
	}

}
