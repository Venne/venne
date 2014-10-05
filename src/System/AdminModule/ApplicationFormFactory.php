<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\System\AdminModule;

use Venne\Forms\IFormFactory;
use Venne\System\Forms\Controls\TextWithSelect;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class ApplicationFormFactory implements \Venne\Forms\IFormFactory
{

	/** @var \Venne\Forms\IFormFactory */
	private $formFactory;

	public function __construct(IFormFactory $formFactory)
	{
		$this->formFactory = $formFactory;
	}

	/**
	 * @return \Nette\Application\UI\Form
	 */
	public function create()
	{
		$form = $this->formFactory->create();

		$nette = $form->addContainer('nette');
		$venne = $form->addContainer('venne');
		$doctrine = $form->addContainer('doctrine');
		/** @var $debugger \Nette\Forms\Container */
		$debugger = $nette->addContainer('debugger');
		$application = $nette->addContainer('application');
		$routing = $nette->addContainer('routing');
		$container = $nette->addContainer('container');
		$security = $nette->addContainer('security');

		/* application */
		$application->setCurrentGroup($form->addGroup('Application'));
		$application->addSelect('catchExceptions', 'Catch exceptions', array(true => 'yes', false => 'no'))->setDefaultValue(true);

		/* session */
		$session = $venne->addContainer('session');
		$session->setCurrentGroup($form->addGroup('Session'));
		$session['savePath'] = (new TextWithSelect('Save path'))->setItems(array('%tempDir%/sessions' => '%tempDir%/sessions'));

		/* debugger */
		$debugger->setCurrentGroup($group = $form->addGroup('Debugger'));
		$debugger->addSelect('strictMode', 'Strict mode')->setItems(array(true => 'yes', false => 'no'));
		$debugger->addText('edit', 'Editor');
		$debugger->addText('browser', 'Browser');
		$debugger->addText('email', 'E-mail for logs')
			->addCondition($form::FILLED)->addRule($form::EMAIL);

		$application->setCurrentGroup($group);
		$application->addCheckbox('debugger', 'Debugger panel in bluescreen')->setDefaultValue(true);

		$routing->setCurrentGroup($group);
		$routing->addCheckbox('debugger', 'Routing panel')->setDefaultValue(true);

		$container->setCurrentGroup($group);
		$container->addCheckbox('debugger', 'DI panel')->setDefaultValue(true);

		$security->setCurrentGroup($group);
		$security->addCheckbox('debugger', 'Security panel')->setDefaultValue(true);

		$doctrine->setCurrentGroup($group);
		$doctrine->addCheckbox('debugger', 'Database panel')->setDefaultValue(true);

		/* session */
		$container = $nette->addContainer('session');
		$container->setCurrentGroup($form->addGroup('Sessions'));
		$container->addCheckbox('autoStart', 'Autostart')->setDefaultValue(false);
		$container->addText('expiration', 'Expiration');
		$container->addText('cookiePath', 'Cookie path')->setDefaultValue('/');
		$container->addText('cookieDomain', 'Cookie domain');

		/* templating */
		$nette->setCurrentGroup($form->addGroup('Templating'));
		$nette->addSelect('xhtml', 'XHTML', array(true => 'yes', false => 'no'))->setDefaultValue(true);

		$form->setCurrentGroup();
		$form->addSubmit('_submit', 'Save');

		return $form;
	}

}
