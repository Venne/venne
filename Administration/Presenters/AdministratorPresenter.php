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
		$modules = array("gd", "iconv", "json", "pdo");
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
		$form->onSuccess[] = $this->formSuccess;
		return $form;
	}


	public function formSuccess($form)
	{
		$user = $this->getUser();
		$values = $form->getValues();

		// login
		$user->setAuthenticator(new \Venne\Security\Authenticator($values['name'], $values['password']));
		$user->login($values['name'], $values['password']);

		/** @var $cache \Nette\Caching\Cache */
		$cache = new \Nette\Caching\Cache($this->context->nette->templateCacheStorage, 'Nette.Configurator');
		$cache->clean();

		$this->redirect(":Cms:Admin:{$this->getContext()->parameters['administration']['defaultPresenter']}:");
	}
}
