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
use Nette\Object;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class ContentType extends Object implements IContentType
{

	/** @var string */
	protected $name;

	/** @var string */
	protected $entityName;

	/** @var ContentTypeSection[] */
	protected $sections = array();


	public function __construct($name, $entityName)
	{
		$this->name = $name;
		$this->entityName = $entityName;
	}


	/**
	 * @param string $name
	 * @param \Nette\Callback $formFactory
	 */
	public function addSection($name, $formFactory)
	{
		$this->sections[$name] = new ContentTypeSection($name, $formFactory);
	}


	/**
	 * @param string $name
	 * @return bool
	 */
	public function hasSection($name)
	{
		return isset($this->sections[$name]);
	}


	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}


	/**
	 * @return mixed
	 */
	public function getParams()
	{
		return $this->params;
	}


	/**
	 * @return string
	 */
	public function getEntityName()
	{
		return $this->entityName;
	}


	/**
	 * @return array|ContentTypeSection[]
	 */
	public function getSections()
	{
		return $this->sections;
	}
}
