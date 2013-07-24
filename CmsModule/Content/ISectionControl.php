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

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
interface ISectionControl
{

	/**
	 * @param ExtendedPageEntity $entity
	 */
	public function setEntity(ExtendedPageEntity $entity);


	/**
	 * @return mixed
	 */
	public function getEntity();
}
