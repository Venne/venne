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

use CmsModule\Forms\SystemDatabaseFormFactory;
use DeploymentModule\DeploymentManager;
use DoctrineModule\DI\ConnectionCheckerFactory;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 *
 * @secured
 */
class DatabasePresenter extends BasePresenter
{

	/** @persistent */
	public $backup;

	/** @var SystemDatabaseFormFactory */
	protected $databaseForm;

	/** @var DeploymentManager */
	protected $deploymentManager;

	/** @var ConnectionCheckerFactory */
	protected $connectionCheckerFactory;


	/**
	 * @param SystemDatabaseFormFactory $databaseForm
	 * @param DeploymentManager $deploymentManager
	 * @param ConnectionCheckerFactory $connectionCheckerFactory
	 */
	public function inject(
		SystemDatabaseFormFactory $databaseForm,
		DeploymentManager $deploymentManager,
		ConnectionCheckerFactory $connectionCheckerFactory
	){
		$this->databaseForm = $databaseForm;
		$this->deploymentManager = $deploymentManager;
		$this->connectionCheckerFactory = $connectionCheckerFactory;
	}


	public function handleLoad()
	{
		if ($this->backup && $this->connectionCheckerFactory->invoke() && count($this->getEntityManager()->getConnection()->getSchemaManager()->listTables()) == 0) {
			$this->deploymentManager->loadBackup($this->backup);
		}


		$this->redirect('this', array('backup' => NULL));
	}


	protected function createComponentSystemDatabaseForm()
	{
		$form = $this->databaseForm->invoke();
		$form->onSuccess[] = $this->systemDatabaseForm;
		return $form;
	}


	public function systemDatabaseForm($form)
	{
		if (isset($form['_backup']) && $form['_backup']->value) {
			$this->redirect('load!', array('backup' => $form['_backup']->value));
		}

		$this->flashMessage($this->translator->translate('Database settings has been updated.'), 'success');
		$this->redirect('this');
	}

}
