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
use Venne\Security\User\User;
use Venne\System\AdminModule\Invitation\InvitationControlFactory;
use Venne\System\Components\AdminGrid\Form;
use Venne\System\Components\AdminGrid\IAdminGridFactory;
use Venne\System\Invitation\Invitation;

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

	/** @var \Nette\Security\User */
	private $netteUser;

	/** @var \Venne\Security\AdminModule\InvitationFormService */
	private $invitationFormService;

	/** @var \Venne\System\AdminModule\Invitation\InvitationControlFactory */
	private $invitationControlFactory;

	public function __construct(
		EntityManager $entityManager,
		IAdminGridFactory $adminGridFactory,
		ITranslator $translator,
		NetteUser $user,
		InvitationFormService $invitationFormService,
		InvitationControlFactory $invitationControlFactory
	) {
		$this->invitationRepository = $entityManager->getRepository(Invitation::class);
		$this->userRepository = $entityManager->getRepository(User::class);
		$this->adminGridFactory = $adminGridFactory;
		$this->translator = $translator;
		$this->netteUser = $user;
		$this->invitationFormService = $invitationFormService;
		$this->invitationControlFactory = $invitationControlFactory;
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
			->andWhere('a.author = :author')->setParameter('author', $this->netteUser->getIdentity()->getId());

		$table = $admin->getTable();
		$table->setModel(new Doctrine($qb));
		$table->setTranslator($this->translator);
		$table->addColumnText('email', 'E-mail')
			->setSortable()
			->getCellPrototype()->width = '60%';
		$table->getColumn('email')
			->setFilterText()->setSuggestion();

		$table->addColumnText('type', 'Type')
			->setCustomRender(function (Invitation $invitation) {
				return $invitation->getRegistration()->getName();
			})
			->getCellPrototype()->width = '40%';

		$form = $admin->addForm('invitation', 'Invitation', function (Invitation $invitation = null, Form $form) use ($admin) {
			$control = $this->invitationControlFactory->create();
			$control->onSave[] = function () use ($form, $admin) {
				$form->onSuccess();
				$admin->formSuccess();
			};

			return $control;
		});

		$toolbar = $admin->getNavbar();
		$newSection = $toolbar->addSection('new', 'Create', 'file');

		$deleteAction = $table->addActionEvent('delete', 'Delete');
		$deleteAction->getElementPrototype()->class[] = 'ajax';

		$admin->connectFormWithNavbar($form, $newSection);
		$admin->connectActionAsDelete($deleteAction);

		return $admin;
	}

}
