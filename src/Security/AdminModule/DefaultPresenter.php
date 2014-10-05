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
use Doctrine\ORM\Query;
use Venne\Security\DefaultType\AdminFormFactory;
use Venne\Security\ExtendedUser;
use Venne\Security\SecurityManager;
use Venne\Security\User;
use Venne\System\Components\AdminGrid\Form;
use Venne\System\Components\AdminGrid\IAdminGridFactory;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class DefaultPresenter extends \Nette\Application\UI\Presenter
{

	use \Venne\System\AdminPresenterTrait;

	/**
	 * @var string
	 *
	 * @persistent
	 */
	public $page;

	/**
	 * @var string
	 *
	 * @persistent
	 */
	public $type;

	/** @var \Kdyby\Doctrine\EntityRepository */
	private $userRepository;

	/** @var \Venne\Security\DefaultType\AdminFormFactory */
	private $form;

	/** @var \Venne\Security\AdminModule\ProvidersFormFactory */
	private $providersForm;

	/** @var \Venne\Security\SecurityManager */
	private $securityManager;

	/** @var \Venne\System\Components\AdminGrid\IAdminGridFactory */
	private $adminGridFactory;

	public function __construct(
		EntityManager $entityManager,
		AdminFormFactory $form,
		ProvidersFormFactory $providersForm,
		SecurityManager $securityManager,
		IAdminGridFactory $adminGridFactory
	)
	{
		$this->userRepository = $entityManager->getRepository(User::class);
		$this->form = $form;
		$this->providersForm = $providersForm;
		$this->securityManager = $securityManager;
		$this->adminGridFactory = $adminGridFactory;
	}

	/**
	 * @return \Venne\Security\SecurityManager
	 */
	public function getSecurityManager()
	{
		return $this->securityManager;
	}

	protected function startup()
	{
		parent::startup();

		if (!$this->type) {
			$this->type = key($this->securityManager->getUserTypes());
		}
	}

	/**
	 * @return \Venne\System\Components\AdminGrid\AdminGrid
	 */
	protected function createComponentTable()
	{
		$repository = $this->entityManager->getRepository($this->type);
		$admin = $this->adminGridFactory->create($repository);
		$table = $admin->getTable();
		$table->setTranslator($this->translator);
		$table->setPrimaryKey('user.id');

		// columns
		$table->addColumnText('user.email', 'E-mail')
			->setSortable()
			->getCellPrototype()->width = '100%';
		$table->getColumn('user.email')
			->setFilterText()->setSuggestion();

		// actions
		$table->addActionEvent('edit', 'Edit')
			->getElementPrototype()->class[] = 'ajax';

		$table->addActionEvent('loginProviders', 'Login providers')
			->getElementPrototype()->class[] = 'ajax';

		$type = $this->type;

		$form = $admin->addForm('user', 'User', function (ExtendedUser $extendedUser = null) {
			return $this->getUserType()->getFormService()->getFormFactory($extendedUser ? $extendedUser->getUser()->getId() : null);
		}, Form::TYPE_LARGE);
		$form->onSuccess[] = function (\Nette\Application\UI\Form $form) {
			$this->flashMessage('User has been saved.', 'success');
			$this->redrawControl('flashes');
		};
		$form->onError[] = function () {
			$this->flashMessage('Failed.', 'warning');
			$this->redrawControl('flashes');
		};

		$providerForm = $admin->addForm('loginProviders', 'Login providers', $this->providersForm, null, Form::TYPE_LARGE);

		$admin->connectFormWithAction($form, $table->getAction('edit'), $admin::MODE_PLACE);
		$admin->connectFormWithAction($providerForm, $table->getAction('loginProviders'));

		// Toolbar
		$toolbar = $admin->getNavbar();
		$section = $toolbar->addSection('new', 'Create', 'user');

		foreach ($this->securityManager->getUserTypes() as $type => $value) {
			$admin->connectFormWithNavbar(
				$form,
				$section->addSection(str_replace('\\', '_', $type), $value->getName()),
				$admin::MODE_PLACE
			);
		}

		$table->addActionEvent('delete', 'Delete')
			->getElementPrototype()->class[] = 'ajax';
		$admin->connectActionAsDelete($table->getAction('delete'));

		return $admin;
	}

	/**
	 * @return \Venne\Security\UserType
	 */
	private function getUserType()
	{
		return $this->securityManager->getUserTypeByClass($this->type);
	}

}
