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
use Nette\Caching\IStorage;
use CmsModule\Forms\SystemAccountFormFactory;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class AdministratorPresenter extends BasePresenter
{

	/** @var string */
	protected $appDir;

	/** @var string */
	protected $wwwDir;

	/** @var string */
	protected $tempDir;

	/** @var string */
	protected $dataDir;

	/** @var string */
	protected $resourcesDir;

	/** @var string */
	protected $configDir;

	/** @var array */
	protected $administration;

	/** @var SystemAccountFormFactory */
	protected $accountForm;

	/** @var IStorage */
	protected $cacheStorage;


	/**
	 * @param IStorage $cacheStorage
	 * @param $parameters
	 */
	public function __construct(IStorage $cacheStorage, $administration, $appDir, $wwwDir, $tempDir, $dataDir, $resourcesDir, $configDir)
	{
		parent::__construct();

		$this->appDir = $appDir;
		$this->wwwDir = $wwwDir;
		$this->tempDir = $tempDir;
		$this->dataDir = $dataDir;
		$this->resourcesDir = $resourcesDir;
		$this->configDir = $configDir;
		$this->cacheStorage = $cacheStorage;
		$this->administration = $administration;
	}


	public function injectAccountForm(SystemAccountFormFactory $accountForm)
	{
		$this->accountForm = $accountForm;
	}


	public function startup()
	{
		parent::startup();

		// Resources dir
		if (!file_exists($this->resourcesDir . "/cmsModule")) {
			@symlink("../../vendor/venne/core-module/Resources/public", $this->resourcesDir . "/cmsModule");
		}

		// Extensions
		$modules = array("gd", "iconv", "json", "pdo");
		foreach ($modules as $item) {
			if (!extension_loaded($item)) {
				$this->flashMessage("Module " . $item . " is not enabled.", "warning");
			}
		}

		// Writable
		$paths = array($this->wwwDir . "/public/", $this->dataDir, $this->configDir, $this->appDir . "/proxies", $this->tempDir);
		foreach ($paths as $item) {
			if (!is_writable($item)) {
				$this->flashMessage("Path " . $item . " is not writable.", "warning");
			}
		}
	}


	public function createComponentSystemAccountForm($name)
	{
		$form = $this->accountForm->invoke();
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
		$cache = new \Nette\Caching\Cache($this->cacheStorage, 'Nette.Configurator');
		$cache->clean();

		$this->redirect(":Cms:Admin:{$this->administration['defaultPresenter']}:");
	}
}
