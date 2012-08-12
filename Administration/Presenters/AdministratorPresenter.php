<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace CmsModule\Administration\Presenters;

use Venne;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class AdministratorPresenter extends BasePresenter
{


	public function startup()
	{
		parent::startup();

		// Resources dir
		if (!file_exists($this->context->parameters['resourcesDir'] . "/cmsModule")) {
			@symlink("../../vendor/venne/core-module/Resources/public", $this->context->parameters['resourcesDir'] . "/cmsModule");
		}

		// Extensions
		$modules = array("gd", "gettext", "iconv", "json", "pdo", "pdo_mysql");
		foreach ($modules as $item) {
			if (!extension_loaded($item)) {
				$this->flashMessage("Module " . $item . " is not enabled.", "warning");
			}
		}

		// Writable
		$paths = array($this->getContext()->parameters["wwwDir"] . "/public/", $this->getContext()->parameters["dataDir"] . "/", $this->getContext()->parameters["configDir"] . "/", $this->getContext()->parameters["tempDir"] . "/", $this->getContext()->parameters["appDir"] . "/proxies/", $this->getContext()->parameters["tempDir"]);
		foreach ($paths as $item) {
			if (!is_writable($item)) {
				$this->flashMessage("Path " . $item . " is not writable.", "warning");
			}
		}
	}


	public function createComponentSystemAccountForm($name)
	{
		$form = $this->context->cms->createAccountForm();
		$form->onSuccess[] = function($form)
		{
			$form->presenter->redirect("save!");
		};
		return $form;
	}


	public function handleSave()
	{
		$name = $this->context->parameters['administration']['login']['name'];
		$password = $this->context->parameters['administration']['login']['password'];

		$this->getUser()->login($name, $password);

		/** @var $cache \Nette\Caching\Cache */
		$cache = new \Nette\Caching\Cache($this->context->nette->templateCacheStorage, 'Nette.Configurator');
		$cache->clean();

		$this->redirect(':Cms:Admin:Default:');
	}
}
