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
class ModuleTagsForm extends BaseConfigForm {

	protected $type;

	public function setService($serviceName, $type)
	{
		$this->setRoot("services.$serviceName");
		$this->type = $type;
	}



	public function startup()
	{
		parent::startup();

		$this->addGroup("Basic setup");

		$tags = $this->addContainer("tags");
		$tags->addCheckbox("run", "Autorun");
		$type = $tags->addContainer($this->type);
		$type->addText("priority", "Priority")->addRule(self::INTEGER, "Priority must be integer.");
	}

}
