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

use Kdyby\Doctrine\EntityManager;
use Nette\Localization\ITranslator;
use Venne\System\Components\AdminGrid\IAdminGridFactory;
use Venne\System\Registration;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class RegistrationTableFactory
{

	/** @var \Kdyby\Doctrine\EntityRepository */
	private $registrationRepository;

	/** @var \Venne\System\Components\AdminGrid\IAdminGridFactory */
	private $adminGridFactory;

	/** @var \Nette\Localization\ITranslator */
	private $translator;

	/** @var \Venne\System\AdminModule\RegistrationFormService */
	private $registrationFormService;

	public function __construct(
		EntityManager $entityManager,
		IAdminGridFactory $adminGridFactory,
		ITranslator $translator,
		RegistrationFormService $registrationFormService
	)
	{
		$this->registrationRepository = $entityManager->getRepository(Registration::class);
		$this->adminGridFactory = $adminGridFactory;
		$this->translator = $translator;
		$this->registrationFormService = $registrationFormService;
	}

	/**
	 * @return \Venne\System\Components\AdminGrid\AdminGrid
	 */
	public function create()
	{
		$admin = $this->adminGridFactory->create($this->registrationRepository);

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

		$form = $admin->addForm('registration', 'Registration', function (Registration $registration = null) {
			return $this->registrationFormService->getFormFactory($registration !== null ? $registration->getId() : null);
		});

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
