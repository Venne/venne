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
use Venne\Forms\Mapping\ConfigFormMapper;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class SystemDatabaseForm extends BaseConfigForm
{
	protected $drivers = array(
		'pdo_mysql',
		'pdo_sqlite',
		'pdo_pgsql',
		'pdo_oci',
		'oci8',
		'pdo_sqlsrv',
	);


	public function startup()
	{
		$this->addGroup("Base settings");
		$this->addSelect("driver", "Driver")
			->setItems($this->drivers, false)
			->setDefaultValue("pdo_mysql");

		$this->addGroup("Connection settings");
		$this->addText("host", "Host");
		$this->addText("port", "Port");
		$this->addText("user", "User name");
		$this->addPassword("password", "Password");
		$this->addText("dbname", "Database");
		$this->addTextWithSelect("path", "Path")->setItems(array("%tempDir%/database.db"), false);
		$this->addCheckbox("memory", "Db in memory");
		$this->addTextWithSelect("charset", "Charset")->setItems(array("utf8"), false);
		$this->addTextWithSelect("collation", "Collation")->setItems(array("utf8_general_ci", "utf8_czech_ci"), false);

		parent::startup();
	}


	/**
	 * @param \Nette\ComponentModel\Container $obj
	 */
	protected function attached($obj)
	{
		parent::attached($obj);

		$this->presenter->context->assets->assetManager->addJavascript("@CmsModule/admin/js/systemDatabaseForm.js");
	}


	public function handleError()
	{

	}


	public function fireEvents()
	{
		if (!$this->isSubmitted()) {
			return;
		}

		$values = $this->getValues();
		$host = $values["host"];

		if ($values["driver"] == "pdo_sqlite") {
			$host = $this->presenter->context->expand($host);
		} else {
			$host = "host={$host};dbname={$values["dbname"]}";
		}

		parent::fireEvents();
	}

}
