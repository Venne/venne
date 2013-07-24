<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace CmsModule\Content\Elements;

use CmsModule\Content\Entities\LanguageEntity;
use CmsModule\Content\Entities\LayoutEntity;
use CmsModule\Content\Entities\PageEntity;
use CmsModule\Content\Entities\RouteEntity;
use Doctrine\ORM\Mapping as ORM;
use DoctrineModule\Entities\IdentifiedEntity;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 *
 * @ORM\MappedSuperclass
 *
 * @property-read ElementEntity $element
 */
abstract class ExtendedElementEntity extends IdentifiedEntity
{

	/**
	 * @var ElementEntity
	 * @ORM\ManyToOne(targetEntity="\CmsModule\Content\Elements\ElementEntity", cascade={"persist"})
	 * @ORM\JoinColumn(referencedColumnName="id", onDelete="CASCADE")
	 */
	protected $element;


	public function __construct($name, LayoutEntity $layout = NULL, PageEntity $page = NULL, RouteEntity $route = NULL, LanguageEntity $language = NULL)
	{
		$this->element = new ElementEntity(get_class($this), $name, $layout, $page, $route, $language);
	}


	/**
	 * @return ElementEntity
	 */
	public function getElement()
	{
		return $this->element;
	}
}
