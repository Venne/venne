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

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class AdministrationPage extends Object implements IAdministrationPage
{


	protected $name;
	protected $link;
	protected $params;



	function __construct($name, $link, $params = array())
	{
		$this->name = $name;
		$this->link = $link;
		$this->params = $params;
	}



	public function getName()
	{
		return $this->name;
	}



	public function getLink()
	{
		return $this->link;
	}



	public function getParams()
	{
		return $this->params;
	}

}
