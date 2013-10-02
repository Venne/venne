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
use CmsModule\Module\Installers\CmsInstaller;
use Doctrine\ORM\Tools\SchemaTool;
use Nette\Caching\Storages\MemoryStorage;
use Nette\Loaders\RobotLoader;
use Nette\Reflection\ClassType;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 *
 * @secured
 */
class DatabasePresenter extends BasePresenter
{

	/** @var SystemDatabaseFormFactory */
	protected $databaseForm;


	function injectDatabaseForm(SystemDatabaseFormFactory $databaseForm)
	{
		$this->databaseForm = $databaseForm;
	}


	protected function createComponentSystemDatabaseForm()
	{
		$form = $this->databaseForm->invoke();
		$form->onSuccess[] = $this->save;
		return $form;
	}


	public function save()
	{
		$this->flashMessage($this->translator->translate('Database settings has been updated.'), 'success');
		$this->redirect('this');
	}



}
