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


	/** @persistent */
	public $mode = "";


	/**
	 * @param \Nette\Application\UI\PresenterComponentReflection $element
	 */
	public function checkRequirements($element)
	{
		// check admin account
		if (!$this->context->parameters['administration']['login']['name']){
			if($this->getName() != "Cms:Admin:Administrator") {
				$this->redirect(":Cms:Admin:Administrator:");
			}
			$this->template->hideMenuItems = true;
		}

		// check database
		elseif (!$this->context->createCheckConnection()) {
			if ($this->getName() != "Cms:Admin:Database" && $this->getName() != "Cms:Admin:Login") {
				$this->redirect(":Cms:Admin:Database:");
			}
			$this->template->hideMenuItems = true;
			$this->flashMessage("Database connection not found. Please fix it.", "warning");
		}

		// check login
		elseif (!$this->getUser()->loggedIn && $this->getName() != "Cms:Admin:Login") {
			if ($this->getUser()->logoutReason === \Nette\Security\User::INACTIVITY) {
				$this->flashMessage("You have been logged out due to inactivity. Please login again.", 'info');
			}
			$this->template->hideMenuItems = true;
			$this->redirect(":Cms:Admin:Login:", array('backlink' => $this->getApplication()->storeRequest()));
		}

		// check updates
		else {
			foreach ($this->context->findByTag("module") as $module => $item) {
				$to = $this->context->{$module}->getVersion();
				$from = (string)$this->context->parameters["modules"][lcfirst(substr($module, 0, -6))]["version"];
				if (!version_compare($to, $from, '==')) {
					if ($this->getName() != "Cms:Admin:Module" && $this->getName() != "Cms:Admin:Login") {
						$this->redirect(":Cms:Admin:Module:");
					}
					$this->flashMessage("Some modules need update or downgrade own database. Please fix it. ({$module}: {$from} => {$to})", "warning");
				}
			}
		}

		parent::checkRequirements($element);
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

		$this->template->adminMenu = new \CmsModule\Events\AdminEventArgs;
		$this->context->eventManager->dispatchEvent(\CmsModule\Events\AdminEvents::onAdminMenu, $this->template->adminMenu);
		$this->template->adminMenu = $this->template->adminMenu->getNavigations();
	}
}

