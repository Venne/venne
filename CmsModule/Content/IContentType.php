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

use Venne;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
interface IContentType
{

	public function __construct($type, $name, $entityName);



	public function getName();



	public function getType();



	public function getEntityName();



	public function getSections();

}
