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
use CmsModule\Content\Entities\PageEntity;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 * @Entity(repositoryClass="\CmsModule\Content\Repositories\PageRepository")
 * @Table(name="redirectPage")
 * @DiscriminatorEntry(name="redirectPage")
 */
class RedirectPageEntity extends PageEntity
{

	/**
	 * @var PageEntity
	 * @ManyToOne(targetEntity="\CmsModule\Content\Entities\PageEntity")
	 * @joinColumn(onDelete="SET NULL")
	 **/
	protected $page;

	/**
	 * @var string
	 * @Column(type="string", nullable=true)
	 */
	protected $redirectUrl;


	public function __construct()
	{
		parent::__construct();
		$this->mainRoute->type = 'Cms:Redirect:default';
	}


	/**
	 * @return PageEntity
	 */
	public function getPage()
	{
		return $this->page;
	}


	/**
	 * @param PageEntity $page
	 */
	public function setPage(PageEntity $page = NULL)
	{
		$this->page = $page;

		if ($this->page) {
			$this->redirectUrl = NULL;
		}
	}


	/**
	 * @return string
	 */
	public function getRedirectUrl()
	{
		return $this->redirectUrl;
	}


	/**
	 * @param string $redirectUrl
	 */
	public function setRedirectUrl($redirectUrl)
	{
		$this->redirectUrl = $redirectUrl;

		if ($this->redirectUrl) {
			$this->page = NULL;
		}
	}
}
