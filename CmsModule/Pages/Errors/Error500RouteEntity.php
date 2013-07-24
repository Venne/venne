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

use CmsModule\Content\Entities\ExtendedRouteEntity;
use Doctrine\ORM\Mapping as ORM;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 * @ORM\Entity(repositoryClass="\DoctrineModule\Repositories\BaseRepository")
 * @ORM\Table(name="static500Route")
 */
class Error500RouteEntity extends ExtendedRouteEntity
{

	protected function getPresenterName()
	{
		return 'Cms:Pages:Text:Text:default';
	}

	/**
	 * @return string
	 */
	public static function getPageName()
	{
		return 'CmsModule\Pages\Errors\Error500PageEntity';
	}
}
