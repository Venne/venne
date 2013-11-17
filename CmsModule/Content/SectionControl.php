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
class SectionControl extends \Venne\Application\UI\Control implements ISectionControl
{

	/** @var ExtendedPageEntity */
	protected $extendedPage;

	/** @var ExtendedRouteEntity */
	protected $extendedRoute;


	/**
	 * @param ExtendedPageEntity $extendedPage
	 * @deprecated
	 */
	public function setEntity(ExtendedPageEntity $extendedPage)
	{
		trigger_error(__METHOD__ . '() is deprecated, use setExtendedPage() instead.', E_USER_WARNING);

		$this->setExtendedPage($extendedPage);
	}


	/**
	 * @return ExtendedPageEntity
	 * @deprecated
	 */
	public function getEntity()
	{
		trigger_error(__METHOD__ . '() is deprecated, use getExtendedPage() instead.', E_USER_WARNING);

		return $this->getExtendedPage();
	}


	/**
	 * @param ExtendedPageEntity $extendedPage
	 */
	public function setExtendedPage(ExtendedPageEntity $extendedPage)
	{
		$this->extendedPage = $extendedPage;
	}


	/**
	 * @return ExtendedPageEntity
	 */
	public function getExtendedPage()
	{
		return $this->extendedPage;
	}


	/**
	 * @param ExtendedRouteEntity $extendedRoute
	 */
	public function setExtendedRoute(ExtendedRouteEntity $extendedRoute)
	{
		$this->extendedRoute = $extendedRoute;
	}


	/**
	 * @return ExtendedRouteEntity
	 */
	public function getExtendedRoute()
	{
		return $this->extendedRoute;
	}

}
