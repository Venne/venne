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

/**
 * @author Prokop Simek <prokopsimek@seznam.cz>
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class MailerFormFactory implements \Venne\Forms\IFormFactory
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

		$netteMailer = $form->addContainer('nette')->addContainer('mailer');
		$cmsMailer = $form->addContainer('system')->addContainer('mailer');

		$netteMailer->setCurrentGroup($group = $form->addGroup('Mailer'));
		$cmsMailer->setCurrentGroup($group);
		$cmsMailer->addText('senderEmail', 'Sender e-mail')->setDefaultValue('info@venne.cz');
		$cmsMailer->addText('senderName', 'Sender name')->setDefaultValue('Venne');

		$smtp = $netteMailer->addCheckbox('smtp', 'Use SMTP');
		$smtp->addCondition($form::EQUAL, true)->toggle('form-smtp');

		$netteMailer->setCurrentGroup($form->addGroup()->setOption('container', \Nette\Utils\Html::el('div')->id('form-smtp')));

		$netteMailer->addText('host', 'Host')
			->addConditionOn($smtp, $form::EQUAL, true)
			->addRule($form::FILLED, 'Enter host');

		$netteMailer->addText('port', 'Port')
			->addConditionOn($smtp, $form::EQUAL, true)
			->addCondition($form::FILLED)
			->addRule($form::INTEGER, 'Enter number format');
		$netteMailer['port']->setOption('placeholder', '25');

		$netteMailer->addSelect('secure', 'Secure', array('ssl' => 'ssl', 'tls' => 'tls'))
			->setPrompt('-----');

		$netteMailer->addText('username', 'Username')
			->addConditionOn($smtp, $form::EQUAL, true)
			->addCondition($form::FILLED)
			->addRule($form::EMAIL, 'Enter email address');

		$netteMailer->addPassword('password', 'Password');

		$netteMailer->addText('timeout', 'Timeout')
			->addConditionOn($smtp, $form::EQUAL, true)
			->addCondition($form::FILLED)
			->addRule($form::INTEGER, 'Enter number format');

		$form->setCurrentGroup();
		$form->addSubmit('_submit', 'Save');

		return $form;
	}

}
