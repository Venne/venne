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
class SystemForm extends BaseConfigForm
{


	public function startup()
	{
		parent::startup();

		$container = $this->addContainer("administration");
		$container->setCurrentGroup($this->addGroup("Administration settings"));
		$container->addText("routePrefix", "Route prefix");
		$container->addText('defaultPresenter', 'Default presenter');
	}
}
