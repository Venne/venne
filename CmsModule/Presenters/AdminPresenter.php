<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace CmsModule\Presenters;

use Venne;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
abstract class AdminPresenter extends BasePresenter
{

	public function startup()
	{
		// check admin account
		if (!$this->context->parameters['administration']['login']['name']) {
			if ($this->getName() != "Cms:Admin:Administrator") {
				$this->redirect(":Cms:Admin:Administrator:");
			}
			$this->template->hideMenuItems = true;
			$this->flashMessage("Please set administrator account.", "warning", true);
		} // end

		// check login
		elseif (!$this->getUser()->isLoggedIn()) {
			if ($this->getName() != "Cms:Admin:Login") {
				$this->redirect(":Cms:Admin:Login:", array('backlink' => $this->storeRequest()));
			}
			$this->template->hideMenuItems = true;
			if ($this->getUser()->logoutReason === \Nette\Security\IUserStorage::INACTIVITY) {
				$this->flashMessage("You have been logged out due to inactivity. Please login again.", 'info');
			}
		}

		// check database
		elseif (!$this->context->createCheckConnection()) {
			if ($this->getName() != "Cms:Admin:Database") {
				$this->redirect(":Cms:Admin:Database:");
			}
			$this->template->hideMenuItems = true;
			$this->flashMessage("Database connection not found. Please fix it.", "warning", true);
		}

		// check languages
		elseif ($this->context->schemaManager->tablesExist('user') && count($this->context->parameters['website']['languages']) == 0) {
			if ($this->getName() != 'Cms:Admin:Language') {
				$this->redirect(':Cms:Admin:Language:');
			}
			$this->template->hideMenuItems = true;
			$this->flashMessage("Please enter at least one language.", "warning", true);
		}

		parent::startup();

		if ($this->getParameter('mode') == self::MODE_EDIT) {
			$this->invalidateControl('panel');
		}
	}


	protected function checkLanguage()
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
	public function formatLayoutTemplateFiles()
	{
		return array($this->getContext()->parameters['modules']['cms']['path'] . '/layouts/administration.latte');
	}


	/**
	 * Common render method.
	 *
	 * @return void
	 */
	public function beforeRender()
	{
		parent::beforeRender();

		$this["head"]->setTitle("Venne:CMS");
		$this["head"]->setRobots($this["head"]::ROBOTS_NOINDEX | $this["head"]::ROBOTS_NOFOLLOW);
	}
}

