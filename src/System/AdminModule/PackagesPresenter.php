<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\System\AdminModule;

use Nette\Application\UI\Presenter;
use Nette\InvalidArgumentException;
use Venne\System\AdminPresenterTrait;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 *
 * @secured
 */
class PackagesPresenter extends Presenter
{

	use AdminPresenterTrait;


	/**
	 * @secured(privilege="show")
	 */
	public function actionDefault()
	{
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
		$this->packageManager->update();
		$this->flashMessage($this->translator->translate('Database has been refreshed.'), 'success');
		$this->redirect('this');
	}


	public function handleClose()
	{
		if (!$this->presenter->isAjax()) {
			$this->redirect('this');
		}

		$this->invalidateControl('content');
		$this->presenter->payload->url = $this->link('this');
	}


	/**
	 * @secured(privilege="edit")
	 */
	public function handleInstall($name, $confirm = false)
	{
		$this->invalidateControl('content');
		$module = $this->packageManager->createInstance($name);

		try {
			$problem = $this->packageManager->testInstall($module);
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
				$this->packageManager->doAction($job->getAction(), $job->getModule());
				$this->flashMessage($this->translator->translate('Module \'%name%\' has been installed.', NULL, array('name' => $job->getModule()->getName())), 'success');
			}
			$this->packageManager->install($module);
			$this->flashMessage($this->translator->translate('Module \'%name%\' has been installed.', NULL, array('name' => $name)), 'success');
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
		$this->invalidateControl('content');
		$module = $this->packageManager->createInstance($name);

		try {
			$problem = $this->packageManager->testUpgrade($module);
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
				$this->packageManager->doAction($job->getAction(), $job->getModule());
				$this->flashMessage($this->translator->translate('Module \'%name%\' has been upgraded.', NULL, array('name' => $job->getModule()->getName())), 'success');
			}
			$this->packageManager->upgrade($module);
			$this->flashMessage($this->translator->translate('Module \'%name%\' has been upgraded.', NULL, array('name' => $name)), 'success');
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
		$this->invalidateControl('content');
		$module = $this->packageManager->createInstance($name);

		try {
			$problem = $this->packageManager->testUninstall($module);
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
				$this->packageManager->doAction($job->getAction(), $job->getModule());
				$this->flashMessage($this->translator->translate('Module \'%name%\' has been uninstalled.', NULL, array('name' => $job->getModule()->getName())), 'success');
			}
			$this->packageManager->uninstall($module);
			$this->flashMessage($this->translator->translate('Module \'%name%\' has been uninstalled.', NULL, array('name' => $name)), 'success');
		} catch (InvalidArgumentException $e) {
			$this->flashMessage($e->getMessage(), 'warning');
		}

		$this->redirect('this');
	}
}
