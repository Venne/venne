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
	) {
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

		$table = $admin->getTable();
		$table->setTranslator($this->translator);
		$table->addColumnText('name', 'Name')
			->setSortable()
			->getCellPrototype()->width = '100%';
		$table->getColumn('name')
			->setFilterText()->setSuggestion();

		$form = $admin->addForm('registration', 'Registration', function (Registration $registration = null) {
			return $this->registrationFormService->getFormFactory($registration !== null ? $registration->getId() : null);
		});

		$toolbar = $admin->getNavbar();
		$newSection = $toolbar->addSection('new', 'Create', 'file');

		$editAction = $table->addActionEvent('edit', 'Edit');
		$editAction->getElementPrototype()->class[] = 'ajax';

		$deleteAction = $table->addActionEvent('delete', 'Delete');
		$deleteAction->getElementPrototype()->class[] = 'ajax';

		$admin->connectFormWithNavbar($form, $newSection);
		$admin->connectFormWithAction($form, $editAction);
		$admin->connectActionAsDelete($deleteAction);

		return $admin;
	}

}
