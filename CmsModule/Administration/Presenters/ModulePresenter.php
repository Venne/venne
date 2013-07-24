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

use Nette\InvalidArgumentException;
use Venne\Module\ModuleManager;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 *
 * @secured
 */
class ModulePresenter extends BasePresenter
{

	/** @var ModuleManager */
	protected $moduleManager;

	/** @var array */
	protected $hiddenModules = array(
		'assets', 'forms', 'doctrine', 'cms', 'translator', 'gedmo'
	);


	/**
	 * @param \Venne\Module\ModuleManager $moduleManager
	 */
	public function __construct(ModuleManager $moduleManager)
	{
		parent::__construct();

		$this->moduleManager = $moduleManager;
	}


	/**
	 * @return \Venne\Module\ModuleManager
	 */
	public function getModuleManager()
	{
		return $this->moduleManager;
	}


	/**
	 * @secured(privilege="show")
	 */
	public function actionDefault()
	{
		$this->template->modules = $this->moduleManager->getModules();
		$this->template->hiddenModules = $this->hiddenModules;
	}


	/**
	 * @secured
	 */
	public function actionEdit()
	{
	}


	/**
	 * @secured(privilege="edit")
	 */
	public function handleSync()
	{
		$this->moduleManager->update();
		$this->flashMessage('Database has been refreshed.', 'success');
		$this->redirect('this');
	}


	/**
	 * @secured(privilege="edit")
	 */
	public function handleInstall($name, $confirm = false)
	{
		$module = $this->moduleManager->createInstance($name);

		try {
			$problem = $this->moduleManager->testInstall($module);
		} catch (InvalidArgumentException $e) {
			$this->flashMessage($e->getMessage(), 'warning');
			$this->redirect('this');
		}

		if (!$confirm && count($problem->getSolutions()) > 0) {
			$this->template->solutions = $problem->getSolutions();
			$this->template->solutionAction = 'install';
			$this->template->solutionModule = $name;
			return;
		}

		try {
			foreach ($problem->getSolutions() as $job) {
				$this->moduleManager->doAction($job->getAction(), $job->getModule());
				$this->flashMessage("Module '{$job->getModule()->getName()}' has been installed.", 'success');
			}
			$this->moduleManager->install($module);
			$this->flashMessage("Module '{$name}' has been installed.", 'success');
		} catch (InvalidArgumentException $e) {
			$this->flashMessage($e->getMessage(), 'warning');
		}

		$this->redirect('this');
	}


	/**
	 * @secured(privilege="edit")
	 */
	public function handleUpgrade($name, $confirm = false)
	{
		$module = $this->moduleManager->createInstance($name);

		try {
			$problem = $this->moduleManager->testUpgrade($module);
		} catch (InvalidArgumentException $e) {
			$this->flashMessage($e->getMessage(), 'warning');
			$this->redirect('this');
		}

		if (!$confirm && count($problem->getSolutions()) > 0) {
			$this->template->solutions = $problem->getSolutions();
			$this->template->solutionAction = 'upgrade';
			$this->template->solutionModule = $name;
			return;
		}

		try {
			foreach ($problem->getSolutions() as $job) {
				$this->moduleManager->doAction($job->getAction(), $job->getModule());
				$this->flashMessage("Module '{$job->getModule()->getName()}' has been installed.", 'success');
			}
			$this->moduleManager->upgrade($module);
			$this->flashMessage("Module '{$name}' has been upgraded.", 'success');
		} catch (InvalidArgumentException $e) {
			$this->flashMessage($e->getMessage(), 'warning');
		}

		$this->redirect('this');
	}


	/**
	 * @secured(privilege="edit")
	 */
	public function handleUninstall($name, $confirm = false)
	{
		$module = $this->moduleManager->createInstance($name);

		try {
			$problem = $this->moduleManager->testUninstall($module);
		} catch (InvalidArgumentException $e) {
			$this->flashMessage($e->getMessage(), 'warning');
			$this->redirect('this');
		}

		if (!$confirm && count($problem->getSolutions()) > 0) {
			$this->template->solutions = $problem->getSolutions();
			$this->template->solutionAction = 'uninstall';
			$this->template->solutionModule = $name;
			return;
		}

		try {
			foreach ($problem->getSolutions() as $job) {
				$this->moduleManager->doAction($job->getAction(), $job->getModule());
				$this->flashMessage("Module '{$job->getModule()->getName()}' has been uninstalled.", 'success');
			}
			$this->moduleManager->uninstall($module);
			$this->flashMessage("Module '{$name}' has been uninstalled.", 'success');
		} catch (InvalidArgumentException $e) {
			$this->flashMessage($e->getMessage(), 'warning');
		}

		$this->redirect('this');
	}
}
