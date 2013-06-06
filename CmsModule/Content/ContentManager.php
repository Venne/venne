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

use CmsModule\Content\IContentType;
use Nette\Callback;
use Nette\DI\Container;
use Nette\Object;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class ContentManager extends Object
{

	/** @var Container */
	protected $context;

	/** @var Closure[] */
	protected $contentTypes = array();

	/** @var array */
	protected $administrationPages = array();


	public function __construct(Container $context)
	{
		$this->context = $context;
	}


	public function addContentType($type, $name, ContentType $contentType)
	{
		$this->contentTypes[$type] = array(
			'name' => $name,
			'factory' => $contentType
		);
	}


	public function addAdministrationPage($name, $description, $category, $link, Callback $administrationPageFactory)
	{
		$this->administrationPages[$link] = array(
			'name' => $name,
			'description' => $description,
			'category' => $category,
			'factory' => $administrationPageFactory
		);
	}


	/**
	 * Get Content Types as array.
	 *
	 * @return ContentType[]
	 */
	public function getContentTypes()
	{
		$ret = array();

		foreach ($this->contentTypes as $type => $item) {
			$ret[$type] = $item['name'];
		}

		return $ret;
	}


	/**
	 * Has content type.
	 *
	 * @param string $link
	 * @return IContentType
	 */
	public function hasContentType($type)
	{
		return isset($this->contentTypes[$type]);
	}


	/**
	 * Get content type.
	 *
	 * @param string $link
	 * @return IContentType
	 */
	public function getContentType($type)
	{
		return $this->contentTypes[$type]['factory'];
	}


	/**
	 * Get Administration pages as array
	 *
	 * @return array
	 */
	public function getAdministrationPages()
	{
		$ret = array();

		foreach ($this->administrationPages as $link => $item) {
			$ret[$item['category']][$link] = $item;
		}

		return $ret;
	}
}

