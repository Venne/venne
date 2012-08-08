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
class SystemApplicationForm extends BaseConfigForm {


	public function startup()
	{
		parent::startup();


		/* containers */
		$nette = $this->addContainer("nette");
		$venne = $this->addContainer("venne");
		$stopwatch = $venne->addContainer("stopwatch");
		$doctrine = $this->addContainer("doctrine");
		$debugger = $this->addContainer("parameters")->addContainer("debugger");


		/* debugger */
		$debugger->setCurrentGroup($group = $this->addGroup("Debugger"));
		$debugger->addSelect("mode", "Mode")->setItems(array("production", "development", "detect"), false);
		$debugger->addSelect("strict", "Strict mode")->setItems(array("yes", "no"), false);
		$debugger->addText("developerIp", "IPs for devel");
		$debugger->addText("logEmail", "E-mail for logs");
		$debugger->addText("emailSnooze", "E-mail interval")->addRule(self::NUMERIC, "E-mail interval must be numeric");
		$stopwatch->setCurrentGroup($group);
		$stopwatch->addCheckbox("debugger", "Stopwatch panel")->setDefaultValue(true);


		/* session */
		$container = $nette->addContainer("session");
		$container->setCurrentGroup($this->addGroup("Sessions"));
		$container->addCheckbox("autoStart", "Autostart")->setDefaultValue(false);
		$container->addTextWithSelect("expiration", "Expiration")->setItems(array("+ 1 day", "+ 10 days", "+ 30 days", "+ 1 year"), false);


		/* application */
		$container = $nette->addContainer("application");
		$group = $this->addGroup("Application");
		$container->setCurrentGroup($group);
		$container->addCheckbox("debugger", "Debugger panel in bluescreen")->setDefaultValue(true);


		/* routing */
		$container = $nette->addContainer("routing");
		$container->setCurrentGroup($this->addGroup("Routing"));
		$container->addCheckbox("debugger", "Routing panel")->setDefaultValue(true);


		/* DI */
		$container = $nette->addContainer("container");
		$container->setCurrentGroup($this->addGroup("Dependency injection"));
		$container->addCheckbox("debugger", "DI panel")->setDefaultValue(true);


		/* security */
		$container = $nette->addContainer("security");
		$container->setCurrentGroup($this->addGroup("Security"));
		$container->addCheckbox("debugger", "Security panel")->setDefaultValue(true);


		/* templating */
		$nette->setCurrentGroup($this->addGroup("Templating"));
		$nette->addSelect("xhtml", "XHTML", array(true => "yes", false => "no"))->setDefaultValue(true);


		/* doctrine */
		$doctrine->setCurrentGroup($this->addGroup("Database"));
		$doctrine->addCheckbox("debugger", "Database panel")->setDefaultValue(true);
	}

}
