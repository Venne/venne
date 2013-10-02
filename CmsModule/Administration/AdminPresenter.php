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
	public $contentLang;

	/** @var bool */
	protected $__installation;

	/** @var string */
	private $_layoutFileCache;


	protected function startup()
	{
		if ($this->contentLang && $this->context->createCheckConnection()) {
			$this->context->cms->pageListener->setLocale($this->contentLang);
		}

		// check admin account
		if (!$this->context->parameters['administration']['login']['name']) {
			if ($this->getName() != 'Cms:Admin:Installation') {
				$this->redirect(':Cms:Admin:Installation:');
			}
			$this->setView('account');
			$this->__installation = TRUE;
			$this->template->hideMenuItems = true;
			$this->flashMessage($this->translator->translate('Please set administrator\'s account.'), 'warning', true);
		} // end

		// check login
		elseif (!$this->getUser()->isLoggedIn()) {
			if ($this->getName() != 'Cms:Admin:Login') {
				$this->redirect(':Cms:Admin:Login:', array('backlink' => $this->storeRequest()));
			}
			$this->template->hideMenuItems = true;
			if ($this->getUser()->logoutReason === \Nette\Security\IUserStorage::INACTIVITY) {
				$this->flashMessage($this->translator->translate('You have been logged out due to inactivity. Please login again.'), 'info');
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
						$this->flashMessage($this->translator->translate('Please fix modules.'), 'warning', true);
						break;
					}
				}
			}

			// check database
			if (!$this->context->createCheckConnection()) {
				if ($this->getName() != 'Cms:Admin:Installation') {
					$this->redirect(':Cms:Admin:Installation:');
				}
				$this->setView('database');
				$this->__installation = TRUE;
				$this->template->hideMenuItems = true;
				$this->flashMessage($this->translator->translate('Database connection not found. Please fix it.'), 'warning', true);
			} // check languages
			elseif ($this->context->schemaManager->tablesExist('users') && count($this->context->parameters['website']['languages']) == 0) {
				if ($this->getName() != 'Cms:Admin:Installation') {
					$this->redirect(':Cms:Admin:Installation:');
				}
				$this->setView('language');
				$this->__installation = TRUE;
				$this->template->hideMenuItems = true;
				$this->flashMessage($this->translator->translate('Please enter at least one language.'), 'warning', true);
			}
		}

		parent::startup();

		if ($this->isPanelOpened()) {
			$this->invalidateControl('panel');
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
		$this->redirect(':Cms:Admin:' . $this->context->parameters['administration']['defaultPresenter'] . ':');
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
		$parameters = $this->getContext()->parameters;
		$module = isset($parameters['administration']['theme']) ? $parameters['administration']['theme'] : 'cms';

		return array(
			$this->getContext()->parameters['modules'][$module]['path'] . '/Resources/administration/@layout.latte',
		);
	}


	/**
	 * Formats view template file names.
	 * @return array
	 */
	public function formatTemplateFiles()
	{
		$name = $this->getName();
		$presenter = substr($name, strrpos(':' . $name, ':'));
		$themeDir = dirname($this->findLayoutTemplateFile());

		return array_merge(array(
			"$themeDir/$presenter/$this->view.latte",
			"$themeDir/$presenter.$this->view.latte",
		), parent::formatTemplateFiles());
	}


	/**
	 * Common render method.
	 *
	 * @return void
	 */
	public function beforeRender()
	{
		parent::beforeRender();

		$this['head']->setTitle('Administration');
		$this['head']->setRobots($this['head']::ROBOTS_NOINDEX | $this['head']::ROBOTS_NOFOLLOW);
	}
}

