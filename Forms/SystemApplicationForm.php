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
		/** @var $debugger \Nette\Forms\Container */
		$debugger = $nette->addContainer("debugger");
		$application = $nette->addContainer("application");
		$routing = $nette->addContainer("routing");
		$container = $nette->addContainer("container");
		$security = $nette->addContainer("security");

		/* application */
		$application->setCurrentGroup($this->addGroup('Application'));
		$application->addSelect("catchExceptions", "Catch exceptions", array(true => "yes", false => "no"))->setDefaultValue(true);

		/* debugger */
		$debugger->setCurrentGroup($group = $this->addGroup("Debugger"));
		$debugger->addSelect("strictMode", "Strict mode")->setItems(array("yes", "no"), false);
		$debugger->addText("edit", "Editor");
		$debugger->addText("browser", "Browser");
		$debugger->addText("email", "E-mail for logs")
			->addCondition(self::FILLED)->addRule(self::EMAIL);

		$stopwatch->setCurrentGroup($group);
		$stopwatch->addCheckbox("debugger", "Stopwatch panel")->setDefaultValue(true);

		$application->setCurrentGroup($group);
		$application->addCheckbox("debugger", "Debugger panel in bluescreen")->setDefaultValue(true);

		$routing->setCurrentGroup($group);
		$routing->addCheckbox("debugger", "Routing panel")->setDefaultValue(true);

		$container->setCurrentGroup($group);
		$container->addCheckbox("debugger", "DI panel")->setDefaultValue(true);

		$security->setCurrentGroup($group);
		$security->addCheckbox("debugger", "Security panel")->setDefaultValue(true);

		$doctrine->setCurrentGroup($group);
		$doctrine->addCheckbox("debugger", "Database panel")->setDefaultValue(true);


		/* session */
		$container = $nette->addContainer("session");
		$container->setCurrentGroup($this->addGroup("Sessions"));
		$container->addCheckbox("autoStart", "Autostart")->setDefaultValue(false);
		$container->addTextWithSelect("expiration", "Expiration")->setItems(array("+ 1 day", "+ 10 days", "+ 30 days", "+ 1 year"), false);

		/* templating */
		$nette->setCurrentGroup($this->addGroup("Templating"));
		$nette->addSelect("xhtml", "XHTML", array(true => "yes", false => "no"))->setDefaultValue(true);
	}

}
