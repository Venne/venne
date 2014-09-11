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

	/**
	 * @var string
	 *
	 * @persistent
	 */
	public $sideComponent;

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
		JsControlFactory $jsControlFactory
	)
	{
		$this->administrationManager = $administrationManager;
		$this->entityManager = $entityManager;
		$this->packageManager = $packageManager;
		$this->application = $application;
		$this->cssControlFactory = $cssControlFactory;
		$this->jsControlFactory = $jsControlFactory;
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
			if ($this->getName() != 'System:Admin:Login') {
				$this->forward(':System:Admin:Login:', array('backlink' => $this->storeRequest()));
			}
			if ($this->getUser()->logoutReason === \Nette\Security\IUserStorage::INACTIVITY) {
				$this->flashMessage($this->translator->translate('You have been logged out due to inactivity. Please login again.'), 'info');
			}
		}

		if ($this->getParameter('do') === null) {
			$this->redrawControl('navigation');
			$this->redrawControl('sideComponent-navigation');
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

		$this->redirect(':' . $this->administrationManager->defaultPresenter . ':');
	}

	public function handleChangeSideComponent()
	{
		$this->redrawControl('sideComponent');
		$this->redirect('this');
	}

	/**
	 * @return \Nette\Application\UI\Control
	 */
	protected function createComponentPanel()
	{
		$sideComponents = $this->getAdministrationManager()->getSideComponents();

		$control = $sideComponents[$this->sideComponent]['factory']->create();

		return $control;
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

}
