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

use FormsModule\Mappers\ConfigMapper;
use Venne\Forms\Form;
use Venne\Forms\FormFactory;

/**
 * @author Prokop Simek <prokopsimek@seznam.cz>
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class SystemMailerFormFactory extends FormFactory
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
		$mapper->setRoot('nette.mailer');
		return $mapper;
	}


	/**
	 * @param Form $form
	 */
	protected function configure(Form $form)
	{
		$form->addGroup('Mailer');
		$smtp = $form->addCheckbox('smtp', 'Use SMTP');
		$smtp->addCondition($form::EQUAL, TRUE)->toggle('form-smtp');

		$form->addGroup()->setOption('id', 'form-smtp');

		$form->addText('host', 'Host')
			->addConditionOn($smtp, $form::EQUAL, TRUE)
			->addRule($form::FILLED, 'Enter host');

		$form->addText('port', 'Port')
			->addConditionOn($smtp, $form::EQUAL, TRUE)
			->addCondition($form::FILLED)
			->addRule($form::INTEGER, 'Enter number format');
		$form['port']->setOption('placeholder', '25');

		$form->addSelect('secure', 'Secure', array('ssl' => 'ssl', 'tls' => 'tls'))
			->setPrompt('-----');

		$form->addText('username', 'Username')
			->addConditionOn($smtp, $form::EQUAL, TRUE)
			->addCondition($form::FILLED)
			->addRule($form::EMAIL, 'Enter email address');


		$form->addPassword('password', 'Password');

		$form->addText('timeout', 'Timeout')
			->addConditionOn($smtp, $form::EQUAL, TRUE)
			->addCondition($form::FILLED)
			->addRule($form::INTEGER, 'Enter number format');

		$form->setCurrentGroup();
		$form->addSaveButton('Save');
	}
}
