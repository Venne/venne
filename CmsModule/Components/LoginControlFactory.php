<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace CmsModule\Components;

use Nette\Callback;
use Nette\Object;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class LoginControlFactory extends Object
{

	/** @var Callback */
	private $loginControlFactory;


	/**
	 * @param Callback $loginControlFactory
	 */
	public function __construct(Callback $loginControlFactory)
	{
		$this->loginControlFactory = $loginControlFactory;
	}


	/**
	 * @return LoginControl
	 */
	public function create()
	{
		return Callback::create($this->loginControlFactory)->invoke();
	}

}
