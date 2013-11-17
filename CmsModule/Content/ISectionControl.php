<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace CmsModule\Content;

use CmsModule\Content\Entities\ExtendedPageEntity;
use CmsModule\Content\Entities\ExtendedRouteEntity;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
interface ISectionControl
{

	/**
	 * @param ExtendedPageEntity $extendedPage
	 */
	public function setExtendedPage(ExtendedPageEntity $extendedPage);


	/**
	 * @return ExtendedPageEntity
	 */
	public function getExtendedPage();


	/**
	 * @param ExtendedRouteEntity $extendedRoute
	 */
	public function setExtendedRoute(ExtendedRouteEntity $extendedRoute);


	/**
	 * @return ExtendedRouteEntity
	 */
	public function getExtendedRoute();
}
