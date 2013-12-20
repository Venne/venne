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

use CmsModule\Administration\Components\AdminGrid\AdminGrid;
use DeploymentModule\DeploymentManager;
use Grido\DataSources\ArraySource;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 *
 * @secured
 */
class BackupsPresenter extends BasePresenter
{

	/** @var DeploymentManager */
	private $deploymentManager;


	/**
	 * @param DeploymentManager $deploymentManager
	 */
	public function inject(
		DeploymentManager $deploymentManager
	)
	{
		$this->deploymentManager = $deploymentManager;
	}


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
	 * @secured
	 */
	public function actionRemove()
	{
	}


	private function getBackups()
	{
		$ret = array();

		foreach ($this->deploymentManager->getBackups() as $name => $values) {
			$ret[] = array('id' => $name, 'name' => $name) + $values;
		}

		return $ret;
	}


	public function getBackupName()
	{
		$max = 0;

		foreach ($this->deploymentManager->getBackups() as $name => $values) {
			if (substr($name, 0, 7) == 'backup-') {
				$name = explode('-', $name, 2);
				$v = intval($name[1]);
				if ($v > $max) {
					$max = $v;
				}
			}
		}
		return 'backup-' . ($max + 1);
	}


	protected function createComponentTable()
	{
		$_this = $this;
		$admin = new AdminGrid;
		$deploymentManager = $this->deploymentManager;

		// columns
		$table = $admin->getTable();
		$table->setModel(new ArraySource($this->getBackups()));
		$table->setTranslator($this->translator);
		$table->addColumnText('name', 'Name')
			->setCustomRender(function ($items) use ($_this) {
				return $items['name'] ? : $_this->translator->translate('untitled');
			})
			->setSortable()
			->getCellPrototype()->width = '80%';

		$table->addColumnDate('date', 'Date', 'd.m.Y - H:i:s')
			->setSortable()
			->getCellPrototype()->width = '20%';

		// actions
		if ($this->isAuthorized('edit')) {

			// Toolbar
			$toolbar = $admin->getNavbar();
			$toolbar->addSection('new', 'Create backup', 'file')->onClick[] = function () use ($_this, $deploymentManager, $admin, $table) {
				$deploymentManager->createBackup($_this->getBackupName());
				$admin->invalidateControl('table');
				$_this->payload->url = $_this->link('this');
				$table->setModel(new ArraySource($_this->getBackups()));
			};


			$table->addAction('load', 'Load')
				->getElementPrototype()->class[] = 'ajax';

			$table->getAction('load')->onClick[] = function ($button, $id) use ($_this, $deploymentManager, $admin, $table) {
				$deploymentManager->loadBackup($id);
				$_this->redirect('this');
			};
		}

		if ($this->isAuthorized('remove')) {
			$table->addAction('delete', 'Delete')
				->getElementPrototype()->class[] = 'ajax';

			$table->getAction('delete')->onClick[] = function ($button, $id) use ($_this, $deploymentManager, $admin, $table) {
				$deploymentManager->removeBackup($id);
				$admin->invalidateControl('table');
				$_this->payload->url = $_this->link('this');
				$table->setModel(new ArraySource($_this->getBackups()));
			};
		}

		return $admin;
	}
}
