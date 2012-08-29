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


	/**
	 * @param Form $form
	 */
	protected function configure(Form $form)
	{
		$form->addGroup("Base settings");
		$form->addSelect("driver", "Driver")
			->setItems($this->drivers, false)
			->setDefaultValue("pdo_mysql");

		$form->addGroup("Connection settings");
		$form->addText("host", "Host");
		$form->addText("port", "Port");
		$form->addText("user", "User name");
		$form->addPassword("password", "Password");
		$form->addText("dbname", "Database");
		$form->addText /*WithSelect*/
		("path", "Path"); //->setItems(array("%tempDir%/database.db"), false);
		$form->addCheckbox("memory", "Db in memory");
		$form->addText /*WithSelect*/
		("charset", "Charset"); //->setItems(array("utf8"), false);
		$form->addText /*WithSelect*/
		("collation", "Collation"); //->setItems(array("utf8_general_ci", "utf8_czech_ci"), false);

		$form->addSubmit('_submit', 'Save');
	}
}
