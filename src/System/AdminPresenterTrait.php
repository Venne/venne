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
use Venne\Packages\PackageManager;
use Venne\System\AdminModule\Components\ITrayControlFactory;
use Venne\System\AdminModule\Components\SideComponentsControlFactory;
use Venne\System\Components\CssControlFactory;
use Venne\System\Components\JsControlFactory;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
trait AdminPresenterTrait
{

	use \Venne\System\UI\PresenterTrait;
	use \Venne\System\AjaxControlTrait;
	use \Venne\Widgets\WidgetsControlTrait;

	/** @var \Venne\System\AdministrationManager */
	private $administrationManager;

	/** @var \Kdyby\Doctrine\EntityManager */
	private $entityManager;

	/** @var \Venne\Packages\PackageManager */
	private $packageManager;

	/** @var \Nette\Application\Application */
	private $application;

	/** @var \Venne\System\Components\CssControlFactory */
	private $cssControlFactory;

	/** @var \Venne\System\Components\JsControlFactory */
	private $jsControlFactory;

	/** @var \Venne\System\AdminModule\Components\ITrayControlFactory */
	private $trayControlFactory;

	/** @var \Venne\System\AdminModule\Components\SideComponentsControlFactory */
	private $sideComponentsControlFactory;

	/** @var bool */
	private $secured = true;

	/**
	 * @param bool $secured
	 */
	public function setSecured($secured)
	{
		$this->secured = (bool) $secured;
	}

	/**
	 * @return bool
	 */
	public function getSecured()
	{
		return $this->secured;
	}

	public function injectAdminPresenter(
		AdministrationManager $administrationManager,
		EntityManager $entityManager,
		PackageManager $packageManager,
		Application $application,
		CssControlFactory $cssControlFactory,
		JsControlFactory $jsControlFactory,
		ITrayControlFactory $trayControlFactory,
		SideComponentsControlFactory $sideComponentsControlFactory
	) {
		$this->administrationManager = $administrationManager;
		$this->entityManager = $entityManager;
		$this->packageManager = $packageManager;
		$this->application = $application;
		$this->cssControlFactory = $cssControlFactory;
		$this->jsControlFactory = $jsControlFactory;
		$this->trayControlFactory = $trayControlFactory;
		$this->sideComponentsControlFactory = $sideComponentsControlFactory;
	}

	/**
	 * @return \Venne\System\AdministrationManager
	 */
	public function getAdministrationManager()
	{
		return $this->administrationManager;
	}

	/**
	 * @return \Kdyby\Doctrine\EntityManager
	 */
	public function getEntityManager()
	{
		return $this->entityManager;
	}

	/**
	 * @return \Nette\Localization\ITranslator
	 */
	public function getTranslator()
	{
		return $this->translator;
	}

	/**
	 * @return \Venne\Packages\PackageManager
	 */
	public function getPackageManager()
	{
		return $this->packageManager;
	}

	/**
	 * @param mixed $element
	 */
	public function checkRequirements($element)
	{
		$this->application->errorPresenter = 'Admin:Error';

		parent::checkRequirements($element);

		// check login
		if ($this->secured && !$this->getUser()->isLoggedIn()) {
			if ($this->getName() !== 'Admin:System:Login') {
				if ($this->getUser()->getLogoutReason() === \Nette\Security\IUserStorage::INACTIVITY) {
					$this->flashMessage($this->getTranslator()->translate('You have been logged out due to inactivity. Please login again.'), 'info');
				}

				$this->forward(':Admin:System:Login:', array('backlink' => $this->storeRequest()));
			}
		}

		if ($this->getParameter('do') === null) {
			$this->redrawControl('content');
			$this->redrawControl('header');
			$this->redrawControl('toolbar');
			$this->redrawControl('title');
		}
	}

	public function handleLogout()
	{
		$this->getUser()->logout(true);
		$this->flashMessage($this->translator->translate('Logout success.'), 'success');

		if ($this->isAjax()) {
			$this->redrawControl('navigation');
			$this->redrawControl('content');
			$this->redrawControl('header');
			$this->redrawControl('toolbar');
			$this->redrawControl('title');
		}

		$this->redirect(':Admin:' . $this->administrationManager->defaultPresenter . ':');
	}

	/**
	 * @return \Venne\System\Components\CssControl
	 */
	protected function createComponentAdminCss()
	{
		return $this->cssControlFactory->create();
	}

	/**
	 * @return \Venne\System\Components\JsControl
	 */
	protected function createComponentAdminJs()
	{
		return $this->jsControlFactory->create();
	}

	/**
	 * @return \Venne\System\AdminModule\Components\TrayControl
	 */
	protected function createComponentTray()
	{
		return $this->trayControlFactory->create();
	}

	/**
	 * @return \Venne\System\AdminModule\Components\SideComponentsControl
	 */
	protected function createComponentSideComponents()
	{
		return $this->sideComponentsControlFactory->create();
	}

	/**
	 * @return \Venne\System\AdminModule\Components\SideComponentsControl
	 */
	protected function getSideComponents()
	{
		return $this['sideComponents'];
	}

}
