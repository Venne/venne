<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace CmsModule\Pages\Login;

use CmsModule\Content\Entities\ExtendedPageEntity;
use Doctrine\ORM\Mapping as ORM;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 * @ORM\Entity(repositoryClass="\CmsModule\Content\Repositories\PageRepository")
 * @ORM\Table(name="loginPage")
 */
class PageEntity extends ExtendedPageEntity
{

	/**
	 * @var \CmsModule\Pages\Registration\PageEntity
	 * @ORM\ManyToOne(targetEntity="\CmsModule\Pages\Registration\PageEntity")
	 * @ORM\joinColumn(onDelete="SET NULL")
	 **/
	protected $registration;


	/**
	 * @param \CmsModule\Pages\Registration\PageEntity $registration
	 */
	public function setRegistration($registration)
	{
		$this->registration = $registration;
	}


	/**
	 * @return \CmsModule\Pages\Registration\PageEntity
	 */
	public function getRegistration()
	{
		return $this->registration;
	}
}
