<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace CmsModule\Administration;

use CmsModule\Presenters\BasePresenter;
use Venne\Module\ModuleManager;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
abstract class AdminPresenter extends BasePresenter
{

	/** @persistent */
	public $compact = false;

	/** @persistent */
	public $contentLang;


	public function startup()
	{
		if ($this->contentLang && $this->context->createCheckConnection()) {
			$this->context->cms->pageListener->setLocale($this->contentLang);
		}

		// check admin account
		if (!$this->context->parameters['administration']['login']['name']) {
			if ($this->getName() != 'Cms:Admin:Administrator') {
				$this->redirect(':Cms:Admin:Administrator:');
			}
			$this->template->hideMenuItems = true;
			$this->flashMessage('Please set administrator account.', 'warning', true);
		} // end

		// check login
		elseif (!$this->getUser()->isLoggedIn()) {
			if ($this->getName() != 'Cms:Admin:Login') {
				$this->redirect(':Cms:Admin:Login:', array('backlink' => $this->storeRequest()));
			}
			$this->template->hideMenuItems = true;
			if ($this->getUser()->logoutReason === \Nette\Security\IUserStorage::INACTIVITY) {
				$this->flashMessage('You have been logged out due to inactivity. Please login again.', 'info');
			}
		} else {

			if ($this->context->parameters['debugMode']) {
				$moduleManager = $this->context->venne->moduleManager;
				foreach ($moduleManager->getModules() as $module) {
					if ($moduleManager->getStatus($module) == 'installed' && $module->getVersion() != $this->context->parameters['modules'][$module->getName()][ModuleManager::MODULE_VERSION]) {
						if ($this->getName() != 'Cms:Admin:Module') {
							$this->redirect(':Cms:Admin:Module:');
						}
						$this->template->hideMenuItems = true;
						$this->flashMessage('Please fix modules.', 'warning', true);
						break;
					}
				}
			}

			// check database
			if (!$this->context->createCheckConnection()) {
				if ($this->getName() != 'Cms:Admin:Database') {
					$this->redirect(':Cms:Admin:Database:');
				}
				$this->template->hideMenuItems = true;
				$this->flashMessage('Database connection not found. Please fix it.', 'warning', true);
			} // check languages
			elseif ($this->context->schemaManager->tablesExist('users') && count($this->context->parameters['website']['languages']) == 0) {
				if ($this->getName() != 'Cms:Admin:Language') {
					$this->redirect(':Cms:Admin:Language:', array('table-navbar-id' => 'navbar-new', 'do' => 'table-navbar-click'));
				}
				$this->template->hideMenuItems = true;
				$this->flashMessage('Please enter at least one language.', 'warning', true);
			}
		}

		parent::startup();

		if ($this->getParameter('mode') == self::MODE_EDIT) {

			$this->invalidateControl('panel');
		}

		if ($this->getParameter('do') === NULL && $this->isAjax()) {
			$this->invalidateControl('navigation');
			$this->invalidateControl('content');
			$this->invalidateControl('header');
			$this->invalidateControl('toolbar');
		}
	}


	public
	function handleLogout()
	{
		$this->user->logout(TRUE);
		$this->flashMessage('Logout success');
		$this->redirect('this');
	}


	protected
	function checkLanguage()
	{
		if (!$this->lang) {
			$this->lang = $this->getHttpRequest()->detectLanguage(array('cs', 'en'));
		}
	}


	/**
	 * Formats layout template file names.
	 *
	 * @return array
	 */
	public
	function formatLayoutTemplateFiles()
	{
		return array($this->getContext()->parameters['modules']['cms']['path'] . '/Resources/layouts/administration.latte');
	}


	/**
	 * Common render method.
	 *
	 * @return void
	 */
	public
	function beforeRender()
	{
		parent::beforeRender();

		$this["head"]->setTitle("Administration");
		$this["head"]->setRobots($this["head"]::ROBOTS_NOINDEX | $this["head"]::ROBOTS_NOFOLLOW);
	}
}

