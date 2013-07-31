<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace CmsModule\Pages\Errors;

use CmsModule\Content\Entities\ExtendedPageEntity;
use Doctrine\ORM\Mapping as ORM;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 * @ORM\Entity(repositoryClass="\CmsModule\Content\Repositories\PageRepository")
 * @ORM\Table(name="static403Page")
 */
class Error403PageEntity extends ExtendedPageEntity
{

	public function startup()
	{
		parent::startup();

		$this->page->navigationShow = FALSE;
	}


	/**
	 * @return string
	 */
	protected function getSpecial()
	{
		return '403';
	}


	/**
	 * @return string
	 */
	public static function getMainRouteName()
	{
		return 'CmsModule\Pages\Errors\Error403RouteEntity';
	}
}
