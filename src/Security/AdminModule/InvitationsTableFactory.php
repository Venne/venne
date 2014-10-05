<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Venne\Security\AdminModule;

use Doctrine\ORM\EntityManager;
use Grido\DataSources\Doctrine;
use Nette\Localization\ITranslator;
use Venne\Security\NetteUser;
use Venne\Security\User;
use Venne\System\Components\AdminGrid\IAdminGridFactory;
use Venne\System\Invitation;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class InvitationsTableFactory
{

	/** @var \Kdyby\Doctrine\EntityRepository */
	private $invitationRepository;

	/** @var \Kdyby\Doctrine\EntityRepository */
	private $userRepository;

	/** @var \Venne\System\Components\AdminGrid\IAdminGridFactory */
	private $adminGridFactory;

	/** @var \Nette\Localization\ITranslator */
	private $translator;

	/** @var \Nette\Security\NetteUser */
	private $netteUser;

	/** @var \Venne\Security\AdminModule\InvitationFormService */
	private $invitationFormService;

	public function __construct(
		EntityManager $entityManager,
		IAdminGridFactory $adminGridFactory,
		ITranslator $translator,
		NetteUser $user,
		InvitationFormService $invitationFormService
	)
	{
		$this->invitationRepository = $entityManager->getRepository(Invitation::class);
		$this->userRepository = $entityManager->getRepository(User::class);
		$this->adminGridFactory = $adminGridFactory;
		$this->translator = $translator;
		$this->netteUser = $user;
		$this->invitationFormService = $invitationFormService;
	}

	/**
	 * @return \Venne\System\Components\AdminGrid\AdminGrid
	 */
	public function create()
	{
		$admin = $this->adminGridFactory->create($this->invitationRepository);
		$qb = $this
			->invitationRepository
			->createQueryBuilder('a')
			->andWhere('a.author = :author')->setParameter('author', $this->netteUser->identity->getId());

		// columns
		$table = $admin->getTable();
		$table->setModel(new Doctrine($qb));
		$table->setTranslator($this->translator);
		$table->addColumnText('email', 'E-mail')
			->setSortable()
			->getCellPrototype()->width = '60%';
		$table->getColumn('email')
			->setFilterText()->setSuggestion();

		$table->addColumnText('type', 'Type')
			->setCustomRender(function (Invitation $invitationEntity) {
				return $invitationEntity->registration->getName();
			})
			->getCellPrototype()->width = '40%';

		// actions
		$table->addActionEvent('edit', 'Edit')
			->getElementPrototype()->class[] = 'ajax';

		$form = $admin->addForm('invitation', 'Invitation', function (Invitation $invitation = null) {
			return $this->invitationFormService->getFormFactory($invitation !== null ? $invitation->getId() : null);
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
