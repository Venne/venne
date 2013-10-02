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
class SystemDatabaseFormFactory extends FormFactory
{

	/** @var array */
	protected $drivers = array(
		'pdo_mysql',
		'pdo_sqlite',
		'pdo_pgsql',
		'pdo_oci',
		'oci8',
		'pdo_sqlsrv',
	);

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
		$mapper->setRoot('parameters.database');
		return $mapper;
	}


	protected function getControlExtensions()
	{
		return array(
			new \FormsModule\ControlExtensions\ControlExtension(),
		);
	}


	/**
	 * @param Form $form
	 */
	protected function configure(Form $form)
	{
		$form->addGroup('Basic settings');
		$form->addSelect('driver', 'Driver')
			->setItems($this->drivers, false)
			->setDefaultValue('pdo_mysql');

		$form['driver']->addCondition($form::IS_IN, array('pdo_mysql', 'oci8', 'pdo_oci'))->toggle('group-charset');
		$form['driver']->addCondition($form::IS_IN, array('pdo_pgsql', 'pdo_mysql', 'oci8', 'pdo_oci', 'pdo_sqlsrv'))->toggle('group-connection');
		$form['driver']->addCondition($form::EQUAL, 'pdo_sqlite')->toggle('group-sqlite');

		$form->addGroup('Connection settings');
		$form->addText('user', 'Username');
		$form->addPassword('password', 'Password');

		$form->addGroup()->setOption('id', 'group-connection');
		$form->addText('host', 'Host');
		$form->addText('port', 'Port')->getControlPrototype()->placeholder[] = 'default';
		$form->addText('dbname', 'Database');

		$form->addGroup()->setOption('id', 'group-sqlite');
		$form->addTextWithSelect('path', 'Path')->setItems(array('%tempDir%/database.db'), false);
		$form->addCheckbox('memory', 'Db in memory');

		$form->addGroup()->setOption('id', 'group-charset');
		$form->addTextWithSelect('charset', 'Charset')->setItems(array('utf8'), false);

		$form->addGroup();
		$form->addSubmit('_submit', 'Save');
	}
}
