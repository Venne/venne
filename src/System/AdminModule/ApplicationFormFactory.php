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

use Venne\System\Forms\Controls\TextWithSelect;
use Venne\Forms\IFormFactory;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class ApplicationFormFactory implements IFormFactory
{

	/** @var IFormFactory */
	private $formFactory;


	/**
	 * @param IFormFactory $formFactory
	 */
	public function __construct(IFormFactory $formFactory)
	{
		$this->formFactory = $formFactory;
	}


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
		$application->addSelect('catchExceptions', 'Catch exceptions', array(TRUE => 'yes', FALSE => 'no'))->setDefaultValue(TRUE);

		/* session */
		$session = $venne->addContainer('session');
		$session->setCurrentGroup($form->addGroup('Session'));
		$session['savePath'] = (new TextWithSelect('Save path'))->setItems(array('%tempDir%/sessions' => '%tempDir%/sessions'));

		/* debugger */
		$debugger->setCurrentGroup($group = $form->addGroup('Debugger'));
		$debugger->addSelect('strictMode', 'Strict mode')->setItems(array(TRUE => 'yes', FALSE => 'no'));
		$debugger->addText('edit', 'Editor');
		$debugger->addText('browser', 'Browser');
		$debugger->addText('email', 'E-mail for logs')
			->addCondition($form::FILLED)->addRule($form::EMAIL);

		$application->setCurrentGroup($group);
		$application->addCheckbox('debugger', 'Debugger panel in bluescreen')->setDefaultValue(TRUE);

		$routing->setCurrentGroup($group);
		$routing->addCheckbox('debugger', 'Routing panel')->setDefaultValue(TRUE);

		$container->setCurrentGroup($group);
		$container->addCheckbox('debugger', 'DI panel')->setDefaultValue(TRUE);

		$security->setCurrentGroup($group);
		$security->addCheckbox('debugger', 'Security panel')->setDefaultValue(TRUE);

		$doctrine->setCurrentGroup($group);
		$doctrine->addCheckbox('debugger', 'Database panel')->setDefaultValue(TRUE);


		/* session */
		$container = $nette->addContainer('session');
		$container->setCurrentGroup($form->addGroup('Sessions'));
		$container->addCheckbox('autoStart', 'Autostart')->setDefaultValue(FALSE);
		$container->addText /*WithSelect*/
			('expiration', 'Expiration'); //->setItems(array('+ 1 day', '+ 10 days', '+ 30 days', '+ 1 year'), false);
		$container->addText('cookiePath', 'Cookie path')->setDefaultValue('/');
		$container->addText('cookieDomain', 'Cookie domain');

		/* templating */
		$nette->setCurrentGroup($form->addGroup('Templating'));
		$nette->addSelect('xhtml', 'XHTML', array(TRUE => 'yes', FALSE => 'no'))->setDefaultValue(TRUE);

		$form->setCurrentGroup();
		$form->addSubmit('_submit', 'Save');

		return $form;
	}
}
