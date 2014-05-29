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

use Kdyby\Doctrine\EntityDao;
use Nette\Application\UI\Presenter;
use Nette\Localization\ITranslator;
use Venne\System\AdminPresenterTrait;
use Venne\System\Components\AdminGrid\IAdminGridFactory;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class RegistrationTableFactory
{

	/** @var EntityDao */
	private $dao;

	/** @var RegistrationFormFactory */
	private $formFactory;

	/** @var IAdminGridFactory */
	private $adminGridFactory;

	/** @var ITranslator */
	private $translator;


	public function __construct(
		EntityDao $dao,
		RegistrationFormFactory $formFactory,
		IAdminGridFactory $adminGridFactory,
		ITranslator $translator
	)
	{
		$this->dao = $dao;
		$this->formFactory = $formFactory;
		$this->adminGridFactory = $adminGridFactory;
		$this->translator = $translator;
	}


	public function create()
	{
		$admin = $this->adminGridFactory->create($this->dao);

		// columns
		$table = $admin->getTable();
		$table->setTranslator($this->translator);
		$table->addColumnText('name', 'Name')
			->setSortable()
			->getCellPrototype()->width = '100%';
		$table->getColumn('name')
			->setFilterText()->setSuggestion();

		// actions
		$table->addActionEvent('edit', 'Edit')
			->getElementPrototype()->class[] = 'ajax';

		$form = $admin->createForm($this->formFactory, 'Role');

		$admin->connectFormWithAction($form, $table->getAction('edit'));

		// Toolbar
		$toolbar = $admin->getNavbar();
		$toolbar->addSection('new', 'Create', 'file');
		$admin->connectFormWithNavbar($form, $toolbar->getSection('new'));

		$table->addActionEvent('delete', 'Delete')
			->getElementPrototype()->class[] = 'ajax';
		$admin->connectActionAsDelete($table->getAction('delete'));

		return $admin;
	}

}
