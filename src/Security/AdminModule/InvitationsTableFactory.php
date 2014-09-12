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

use Grido\DataSources\Doctrine;
use Kdyby\Doctrine\EntityDao;
use Nette\Localization\ITranslator;
use Nette\Security\User;
use Venne\System\Components\AdminGrid\IAdminGridFactory;
use Venne\System\InvitationEntity;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class InvitationsTableFactory
{

	/** @var \Kdyby\Doctrine\EntityDao */
	private $invitationDao;

	/** @var \Kdyby\Doctrine\EntityDao */
	private $userDao;

	/** @var \Venne\Security\AdminModule\InvitationFormFactory */
	private $formFactory;

	/** @var \Venne\System\Components\AdminGrid\IAdminGridFactory */
	private $adminGridFactory;

	/** @var \Nette\Localization\ITranslator */
	private $translator;

	/** @var \Nette\Security\User */
	private $user;

	public function __construct(
		EntityDao $invitationDao,
		EntityDao $userDao,
		InvitationFormFactory $formFactory,
		IAdminGridFactory $adminGridFactory,
		ITranslator $translator,
		User $user
	)
	{
		$this->invitationDao = $invitationDao;
		$this->userDao = $userDao;
		$this->formFactory = $formFactory;
		$this->adminGridFactory = $adminGridFactory;
		$this->translator = $translator;
		$this->user = $user;
	}

	/**
	 * @return \Venne\System\Components\AdminGrid\AdminGrid
	 */
	public function create()
	{
		$admin = $this->adminGridFactory->create($this->invitationDao);

		// columns
		$table = $admin->getTable();
		$table->setModel(new Doctrine($this->invitationDao->createQueryBuilder('a')
				->andWhere('a.author = :author')->setParameter('author', $this->user->identity->getId())
		));
		$table->setTranslator($this->translator);
		$table->addColumnText('email', 'E-mail')
			->setSortable()
			->getCellPrototype()->width = '60%';
		$table->getColumn('email')
			->setFilterText()->setSuggestion();

		$table->addColumnText('type', 'Type')
			->setCustomRender(function (InvitationEntity $invitationEntity) {
				return $invitationEntity->registration->getName();
			})
			->getCellPrototype()->width = '40%';

		// actions
		$table->addActionEvent('edit', 'Edit')
			->getElementPrototype()->class[] = 'ajax';

		$form = $admin->createForm($this->formFactory, 'Role', function () {
			return new InvitationEntity(
				$this->userDao->find($this->user->getIdentity()->getId())
			);
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
