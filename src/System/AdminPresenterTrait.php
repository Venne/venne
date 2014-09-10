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

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
trait AdminPresenterTrait
{

	use \Venne\System\UI\PresenterTrait;
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
		Application $application
	)
	{
		$this->administrationManager = $administrationManager;
		$this->entityManager = $entityManager;
		$this->packageManager = $packageManager;
		$this->application = $application;
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

		if ($this->getParameter('do') === null && $this->isAjax()) {
			$this->redrawControl('navigation');
			$this->redrawControl('content');
			$this->redrawControl('header');
			$this->redrawControl('toolbar');
			$this->redrawControl('title');
		}
	}

	public function handleLogout()
	{
		$this->user->logout(true);
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
	 * @param string $id
	 */
	public function handleChangeSideComponent($id)
	{
		if (!$this->isAjax()) {
			$this->redirect('this', array('sideComponent' => $id));
		}

		$this->sideComponent = $id;
		$this->redrawControl('sideComponent');
	}

}
