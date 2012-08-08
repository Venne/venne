<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace CmsModule\Forms;

use Venne;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class SystemAccountForm extends BaseConfigForm {


	public function startup()
	{
		parent::startup();

		$this->addGroup();
		$this->addText("name", "Name");
		$this->addPassword("password", "Password")->setOption("description", "minimal length is 5 char");
		$this->addPassword("_password", "Confirm password");


		$this["name"]->addRule(self::FILLED, 'Enter name');
		$this["password"]->addRule(self::FILLED, 'Enter password')->addRule(self::MIN_LENGTH, 'Password is short', 5);
		$this["_password"]->addRule(self::EQUAL, 'Invalid re password', $this['password']);
	}

}
