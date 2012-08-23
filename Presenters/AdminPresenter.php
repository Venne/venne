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
class AdminPresenter extends BasePresenter
{

	/**
	 * @param \Nette\Application\UI\PresenterComponentReflection $element
	 */
	public function checkRequirements($element)
	{
		// check admin account
		if (!$this->context->parameters['administration']['login']['name']) {
			if ($this->getName() != "Cms:Admin:Administrator") {
				$this->redirect(":Cms:Admin:Administrator:");
			}
			$this->template->hideMenuItems = true;
		} // end

		// check login
		elseif (!$this->getUser()->isLoggedIn()) {
			if ($this->getName() != "Cms:Admin:Login") {
				$this->redirect(":Cms:Admin:Login:", array('backlink' => $this->storeRequest()));
			}
			if ($this->getUser()->logoutReason === \Nette\Security\IUserStorage::INACTIVITY) {
				$this->flashMessage("You have been logged out due to inactivity. Please login again.", 'info');
			}
			$this->template->hideMenuItems = true;
		}

		// check database
		elseif (!$this->context->createCheckConnection()) {
			if ($this->getName() != "Cms:Admin:Database") {
				$this->redirect(":Cms:Admin:Database:");
			}
			$this->template->hideMenuItems = true;
			$this->flashMessage("Database connection not found. Please fix it.", "warning");
		}

		// check languages
		elseif ($this->context->schemaManager->tablesExist('user') && count($this->context->parameters['website']['languages']) == 0) {
			if ($this->getName() != 'Cms:Admin:Language') {
				$this->redirect(':Cms:Admin:Language:');
			}
			$this->template->hideMenuItems = true;
			$this->flashMessage("Please enter at least one language.", "warning");
		}

		parent::checkRequirements($element);
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

