<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace CmsModule\Pages\Sitemap;

use CmsModule\Content\Entities\ExtendedPageEntity;
use Doctrine\ORM\Mapping as ORM;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 * @ORM\Entity(repositoryClass="\CmsModule\Content\Repositories\PageRepository")
 * @ORM\Table(name="sitemapPage")
 *
 * @property \CmsModule\Content\Entities\PageEntity $rootPage
 * @property int $maxDepth
 * @property int $maxWidth
 */
class PageEntity extends ExtendedPageEntity
{

	/**
	 * @var \CmsModule\Content\Entities\PageEntity
	 * @ORM\ManyToOne(targetEntity="\CmsModule\Content\Entities\PageEntity")
	 * @ORM\JoinColumn(referencedColumnName="id", onDelete="SET NULL")
	 */
	protected $rootPage;

	/**
	 * @ORM\Column(type="integer")
	 */
	protected $maxDepth = 5;

	/**
	 * @ORM\Column(type="integer")
	 */
	protected $maxWidth = 30;


	protected function startup()
	{
		parent::startup();

		$this->page->navigationShow = FALSE;
	}


	protected function getSpecial()
	{
		return 'sitemap';
	}


	/**
	 * @param \CmsModule\Content\Entities\PageEntity $rootPage
	 */
	public function setRootPage($rootPage)
	{
		$this->rootPage = $rootPage;
	}


	/**
	 * @return \CmsModule\Content\Entities\PageEntity
	 */
	public function getRootPage()
	{
		return $this->rootPage;
	}


	/**
	 * @param mixed $maxDepth
	 */
	public function setMaxDepth($maxDepth)
	{
		$this->maxDepth = $maxDepth;
	}


	/**
	 * @return mixed
	 */
	public function getMaxDepth()
	{
		return $this->maxDepth;
	}


	/**
	 * @param mixed $maxWidth
	 */
	public function setMaxWidth($maxWidth)
	{
		$this->maxWidth = $maxWidth;
	}


	/**
	 * @return mixed
	 */
	public function getMaxWidth()
	{
		return $this->maxWidth;
	}
}
