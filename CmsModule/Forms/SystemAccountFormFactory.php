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
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class SystemAccountFormFactory extends FormFactory
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
		$mapper->setRoot('cms.administration.login');
		return $mapper;
	}


	/**
	 * @param Form $form
	 */
	protected function configure(Form $form)
	{
		$form->addGroup('Admin account');
		$form->addText('name', 'Name');
		$form->addPassword('password', 'Password')->setOption('description', 'minimal length is 5 char');
		$form->addPassword('_password', 'Confirm password');

		$form['name']->addRule($form::FILLED, 'Enter name');
		$form['password']->addRule($form::FILLED, 'Enter password')->addRule($form::MIN_LENGTH, 'Password is short', 5);
		$form['_password']->addRule($form::EQUAL, 'Invalid re password', $form['password']);

		$form->addSubmit('_submit', 'Save');
	}
}
