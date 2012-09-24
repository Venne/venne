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
use Venne\Forms\FormFactory;
use Venne\Forms\Form;
use FormsModule\Mappers\ConfigMapper;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class SystemApplicationFormFactory extends FormFactory
{


	/** @var ConfigMapper */
	protected $mapper;


	/**
	 * @param ConfigMapper $mapper
	 */
	public function __construct(ConfigMapper $mapper)
	{
		$this->mapper = $mapper;
	}


	protected function getMapper()
	{
		$mapper = clone $this->mapper;
		$mapper->setRoot('');
		return $mapper;
	}


	/**
	 * @param Form $form
	 */
	protected function configure(Form $form)
	{
		/* containers */
		$nette = $form->addContainer("nette");
		$venne = $form->addContainer("venne");
		$stopwatch = $venne->addContainer("stopwatch");
		$doctrine = $form->addContainer("doctrine");
		/** @var $debugger \Nette\Forms\Container */
		$debugger = $nette->addContainer("debugger");
		$application = $nette->addContainer("application");
		$routing = $nette->addContainer("routing");
		$container = $nette->addContainer("container");
		$security = $nette->addContainer("security");

		/* application */
		$application->setCurrentGroup($form->addGroup('Application'));
		$application->addSelect("catchExceptions", "Catch exceptions", array(true => "yes", false => "no"))->setDefaultValue(true);

		/* debugger */
		$debugger->setCurrentGroup($group = $form->addGroup("Debugger"));
		$debugger->addSelect("strictMode", "Strict mode")->setItems(array("yes", "no"), false);
		$debugger->addText("edit", "Editor");
		$debugger->addText("browser", "Browser");
		$debugger->addText("email", "E-mail for logs")
			->addCondition($form::FILLED)->addRule($form::EMAIL);

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
		$container->setCurrentGroup($form->addGroup("Sessions"));
		$container->addCheckbox("autoStart", "Autostart")->setDefaultValue(false);
		$container->addText/*WithSelect*/("expiration", "Expiration");//->setItems(array("+ 1 day", "+ 10 days", "+ 30 days", "+ 1 year"), false);

		/* templating */
		$nette->setCurrentGroup($form->addGroup("Templating"));
		$nette->addSelect("xhtml", "XHTML", array(true => "yes", false => "no"))->setDefaultValue(true);

		$form->addSubmit('_submit', 'Save');
	}
}
