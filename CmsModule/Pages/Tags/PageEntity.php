<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace CmsModule\Pages\Tags;

use CmsModule\Content\Entities\ExtendedPageEntity;
use Doctrine\ORM\Mapping as ORM;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 * @ORM\Entity(repositoryClass="\CmsModule\Content\Repositories\PageRepository")
 * @ORM\Table(name="tags_page")
 */
class PageEntity extends ExtendedPageEntity
{

	/**
	 * @var string
	 * @ORM\Column(type="integer")
	 */
	protected $itemsPerPage = 10;


	/**
	 * @return string
	 */
	public static function getExtendedMainRouteName()
	{
		return static::getReflection()->getNamespaceName() . '\TagsEntity';
	}


	protected function getSpecial()
	{
		return 'tags';
	}


	/**
	 * @param string $itemsPerPage
	 */
	public function setItemsPerPage($itemsPerPage)
	{
		$this->itemsPerPage = $itemsPerPage;
	}


	/**
	 * @return string
	 */
	public function getItemsPerPage()
	{
		return $this->itemsPerPage;
	}
}
