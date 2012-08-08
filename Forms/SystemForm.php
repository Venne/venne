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
class SystemForm extends BaseConfigForm {


	public function startup()
	{
		parent::startup();

		$this->addGroup("Mode settings");
		$this->addSelect("mode", "Mode");

		$container = $this->addContainer("administration");
		$container->setCurrentGroup($this->addGroup("Administration settings"));
		$container->addText("routePrefix", "Route prefix");
	}
	
	
	/**
	 * @param \Nette\ComponentModel\Container $obj
	 */
	protected function attached($obj)
	{
		parent::attached($obj);

		$this['mode']->setItems($this->presenter->context->parameters["environments"], false);
	}

}
