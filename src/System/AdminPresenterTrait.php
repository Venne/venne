<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\System;

use Kdyby\Doctrine\EntityManager;
use Nette\Application\Application;
use Nette\InvalidStateException;
use Nette\Localization\ITranslator;
use Venne\Packages\PackageManager;
use Venne\Security\ExtendedUserEntity;
use Venne\Security\UserEntity;
use Venne\System\UI\PresenterTrait;
use Venne\Widgets\WidgetsControlTrait;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
trait AdminPresenterTrait
{

	use PresenterTrait;
	use WidgetsControlTrait;

	/** @persistent */
	public $lang;

	/** @persistent */
	public $sideComponent;

	/** @var AdministrationManager */
	private $administrationManager;

	/** @var EntityManager */
	private $entityManager;

	/** @var bool */
	private $_translatorInit = FALSE;

	/** @var ExtendedUserEntity */
	private $extendedUser;

	/** @var string */
	private $_layoutFileCache;

	/** @var PackageManager */
	private $packageManager;

	/** @var Application */
	private $application;

	/** @var bool */
	private $secured = TRUE;


	/**
	 * @param boolean $secured
	 */
	public function setSecured($secured)
	{
		$this->secured = (bool)$secured;
	}


	/**
	 * @return boolean
	 */
	public function getSecured()
	{
		return $this->secured;
	}


	public function injectAdminPresenter(
		AdministrationManager $administrationManager,
		EntityManager $entityManager,
		PackageManager $packageManager,
		Application $application
	)
	{
		$this->administrationManager = $administrationManager;
		$this->entityManager = $entityManager;
		$this->packageManager = $packageManager;
		$this->application = $application;
	}


	/**
	 * @return AdministrationManager
	 */
	public function getAdministrationManager()
	{
		return $this->administrationManager;
	}


	/**
	 * @return EntityManager
	 */
	public function getEntityManager()
	{
		return $this->entityManager;
	}


	/**
	 * @return ITranslator
	 */
	public function getTranslator()
	{
		if (!$this->_translatorInit) {
			$this->_translatorInit = TRUE;

			// Language
			$this->checkLanguage();

			// Setup translator
			//$this->translator->setLang($this->lang);
		}

		return $this->translator;
	}


	/**
	 * @return ExtendedUserEntity
	 * @throws InvalidStateException
	 */
	public function getExtendedUser()
	{
		if (!$this->extendedUser) {
			if (!$this->user->isLoggedIn()) {
				throw new InvalidStateException("User is not logged in.");
			}

			if (!$this->user->identity instanceof UserEntity) {
				throw new InvalidStateException("User must be instance of 'Venne\Security\UserEntity'.");
			}

			$this->extendedUser = $this->user->identity->extendedUser;
		}
		return $this->extendedUser;
	}


	/**
	 * @return PackageManager
	 */
	public function getPackageManager()
	{
		return $this->packageManager;
	}


	public function checkRequirements($element)
	{
		$this->application->errorPresenter = 'Admin:Error';

		parent::checkRequirements($element);

		// check login
		if ($this->secured && !$this->getUser()->isLoggedIn()) {
			if ($this->getName() != 'System:Admin:Login') {
				$this->forward(':System:Admin:Login:', array('backlink' => $this->storeRequest()));
			}
			if ($this->getUser()->logoutReason === \Nette\Security\IUserStorage::INACTIVITY) {
				$this->flashMessage($this->translator->translate('You have been logged out due to inactivity. Please login again.'), 'info');
			}
		}

		if ($this->getParameter('do') === NULL && $this->isAjax()) {
			$this->invalidateControl('navigation');
			$this->invalidateControl('content');
			$this->invalidateControl('header');
			$this->invalidateControl('toolbar');
		}
	}


	public function handleLogout()
	{
		$this->user->logout(TRUE);
		$this->flashMessage($this->translator->translate('Logout success'), 'success');
		$this->redirect(':' . $this->administrationManager->defaultPresenter . ':');
	}


	protected function checkLanguage()
	{
		if (!$this->lang) {
			$this->lang = $this->getHttpRequest()->detectLanguage(array('cs', 'en'));
		}
	}


	/**
	 * Finds layout template file name.
	 * @return string
	 */
	public function findLayoutTemplateFile()
	{
		if (!$this->_layoutFileCache) {
			$this->_layoutFileCache = parent::findLayoutTemplateFile();
		}

		return $this->_layoutFileCache;
	}


	/**
	 * Formats layout template file names.
	 * @return array
	 */
	public function formatLayoutTemplateFiles()
	{
		$name = $this->getName();
		$presenter = explode(':', $name);
		unset($presenter[1]);
		$presenter = implode('/', $presenter);

		return array(
			$this->getContext()->parameters['packages'][$this->administrationManager->theme]['path'] . "/Resources/administration/$presenter/@layout.latte",
			$this->getContext()->parameters['packages'][$this->administrationManager->theme]['path'] . '/Resources/administration/@layout.latte',
		);
	}


	/**
	 * Formats view template file names.
	 * @return array
	 */
	public function formatTemplateFiles()
	{
		$name = $this->getName();
		$presenter = explode(':', $name);
		unset($presenter[1]);
		$presenter = implode('/', $presenter);

		return array_merge(array(
			$this->getContext()->parameters['packages'][$this->administrationManager->theme]['path'] . "/Resources/administration/$presenter/$this->view.latte",
			$this->getContext()->parameters['packages'][$this->administrationManager->theme]['path'] . "/Resources/administration/$presenter.$this->view.latte",
		), parent::formatTemplateFiles());
	}

}

