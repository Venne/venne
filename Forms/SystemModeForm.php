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
class SystemModeForm extends \Venne\Application\UI\Form {


	/** @var \CmsModule\Services\ConfigBuilder */
	protected $configManager;

	/** @var string */
	protected $mode;

	/** @var string */
	protected $configDir;



	/**
	 * @param \CmsModule\Services\ConfigBuilder $configManager
	 * @param \Nette\DI\Container $container
	 */
	public function __construct(\CmsModule\Services\ConfigBuilder $configManager, \Nette\DI\Container $container)
	{
		$this->configManager = $configManager;
		$this->mode = $container->parameters["mode"];
		$this->configDir = $container->parameters["configDir"];
		parent::__construct();
	}



	public function startup()
	{
		parent::startup();

		$this->addGroup();
		$this->addText("name", "Name")->setDefaultValue((string)$this->mode);
	}



	public function handleSuccess()
	{
		if ($this->mode !== NULL) {
			$key = array_search($this->mode, ((array)$this->configManager["parameters"]["environments"]));
			$this->configManager["parameters"]["modes"][$key] = $this["name"]->value;
			rename($this->configDir . "/config." . $this->mode . ".neon", $this->configDir . "/config." . $this["name"]->value . ".neon");
		} else {
			file_put_contents($this->configDir . "/config." . $this["name"]->value . ".neon", "");
			$key = count($this->configManager["parameters"]["modes"]);
			$this->configManager["parameters"]["modes"][$key] = $this["name"]->value;
		}

		if ($this->configManager["parameters"]["mode"] == $this->mode) {
			$this->configManager["parameters"]["mode"] = $this["name"]->value;
		}

		$this->configManager->save();
	}

}
