<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace CmsModule;

use Venne;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
abstract class AdminPresenterTest extends \Venne\Testing\SeleniumTestCase
{
//	protected function setUp()
//	{
//		$this->setBrowser("*chrome");
//		$this->setBrowserUrl("http://localhost/sandbox/www");
//	}


	public function login()
	{
		$this->open($this->basePath . '/admin');

		$this->type("id=frmsignInForm-username", "root");
		$this->type("id=frmsignInForm-password", "tajne");
		$this->clickAndWait("id=frmsignInForm-_submit");
	}


	public function logout()
	{
		$this->open($this->basePath . '/admin?do=logout');
	}
}

