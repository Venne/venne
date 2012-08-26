<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace CmsModule\Administration;

use Venne;
use Nette\Object;
use Nette\DI\Container;
use Nette\Callback;
use CmsModule\Content\IContentType;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class AdministrationManager extends Object
{


	/** @var Container */
	protected $context;

	/** @var array */
	protected $administrationPages = array();


	public function __construct(Container $context)
	{
		$this->context = $context;
	}


	public function addAdministrationPage($name, $description, $category, $link)
	{
		$this->administrationPages[$link] = array(
			'name' => $name,
			'description' => $description,
			'category' => $category,
		);
	}


	/**
	 * Get Administration pages as array
	 *
	 * @return array
	 */
	public function getAdministrationPages()
	{
		return $this->administrationPages;
	}


	/**
	 * Get Administration pages as array
	 *
	 * @return array
	 */
	public function getAdministrationNavigation()
	{
		$ret = array();

		foreach ($this->administrationPages as $link => $item) {
			$ret[$item['category']][$link] = $item;
		}

		return $ret;
	}
}

