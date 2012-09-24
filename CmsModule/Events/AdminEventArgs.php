<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace CmsModule\Events;

use Nette;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class AdminEventArgs extends \Doctrine\Common\EventArgs {


	/** @var array */
	private $list = array();



	/**
	 * @param \StaModule\NavigationEntity $entity
	 */
	public function addNavigation(\CmsModule\Entities\NavigationEntity $entity)
	{
		$this->list[] = $entity;
	}



	/**
	 * @return array
	 */
	public function getNavigations()
	{
		return $this->list;
	}

}
