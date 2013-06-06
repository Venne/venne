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

use Nette\Object;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class ContentTypeSection extends Object
{

	/** @var string */
	protected $name;

	/** @var \Nette\Callback */
	protected $formFactory;


	public function __construct($name, $formFactory)
	{
		$this->name = $name;
		$this->formFactory = $formFactory;
	}


	/**
	 * @return \Nette\Callback
	 */
	public function getFormFactory()
	{
		return $this->formFactory;
	}


	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}
}
