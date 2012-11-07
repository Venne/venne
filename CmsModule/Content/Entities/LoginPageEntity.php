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
 * @Table(name="loginPage")
 * @DiscriminatorEntry(name="loginPage")
 */
class LoginPageEntity extends PageEntity
{

	/**
	 * @var PageEntity
	 * @ManyToOne(targetEntity="\CmsModule\Content\Entities\PageEntity")
	 * @joinColumn(onDelete="SET NULL")
	 **/
	protected $page;

	/**
	 * @var RegistrationPageEntity
	 * @ManyToOne(targetEntity="\CmsModule\Content\Entities\RegistrationPageEntity")
	 * @joinColumn(onDelete="SET NULL")
	 **/
	protected $registration;


	public function __construct()
	{
		parent::__construct();

		$this->mainRoute->type = 'Cms:Login:default';
	}


	/**
	 * @param PageEntity $page
	 */
	public function setPage($page)
	{
		$this->page = $page;
	}


	/**
	 * @return PageEntity
	 */
	public function getPage()
	{
		return $this->page;
	}


	/**
	 * @param \CmsModule\Content\Entities\RegistrationPageEntity $registration
	 */
	public function setRegistration($registration)
	{
		$this->registration = $registration;
	}


	/**
	 * @return \CmsModule\Content\Entities\RegistrationPageEntity
	 */
	public function getRegistration()
	{
		return $this->registration;
	}
}
