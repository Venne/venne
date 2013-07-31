<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace CmsModule\Pages\Redirect;

use CmsModule\Content\Entities\ExtendedPageEntity;
use Doctrine\ORM\Mapping as ORM;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 * @ORM\Entity(repositoryClass="\DoctrineModule\Repositories\BaseRepository")
 * @ORM\Table(name="redirectPage")
 */
class PageEntity extends ExtendedPageEntity
{

	/**
	 * @var PageEntity
	 * @ORM\ManyToOne(targetEntity="\CmsModule\Content\Entities\PageEntity")
	 * @ORM\joinColumn(onDelete="SET NULL")
	 **/
	protected $redirect;

	/**
	 * @var string
	 * @ORM\Column(type="string", nullable=true)
	 */
	protected $redirectUrl;


	/**
	 * @return PageEntity
	 */
	public function getRedirect()
	{
		return $this->redirect;
	}


	/**
	 * @param \CmsModule\Content\Entities\PageEntity $page
	 */
	public function setRedirect(\CmsModule\Content\Entities\PageEntity $page = NULL)
	{
		$this->redirect = $page;

		if ($this->redirect) {
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
			$this->redirect = NULL;
		}
	}
}
