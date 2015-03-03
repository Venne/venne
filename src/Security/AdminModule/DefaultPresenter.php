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
use Venne\Security\User\DefaultType\AdminFormFactory;
use Venne\Security\User\ExtendedUser;
use Venne\Security\SecurityManager;
use Venne\Security\User\User;
use Venne\System\Components\AdminGrid\Form;
use Venne\System\Components\AdminGrid\IAdminGridFactory;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class DefaultPresenter extends \Nette\Application\UI\Presenter
{

	use \Venne\System\AdminPresenterTrait;

	/** @var string */
	private $page;

	/** @var string */
	private $type;

	/** @var \Kdyby\Doctrine\EntityRepository */
	private $userRepository;

	/** @var \Venne\Security\User\DefaultType\AdminFormFactory */
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
	) {
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

	/**
	 * @return \Venne\System\Components\AdminGrid\AdminGrid
	 */
	protected function createComponentTable()
	{
		$admin = $this->adminGridFactory->create($this->userRepository);
		$table = $admin->getTable();
		$table->setTranslator($this->translator);

		$table->addColumnText('email', 'E-mail')
			->setSortable()
			->getCellPrototype()->width = '100%';
		$table->getColumn('email')
			->setFilterText()->setSuggestion();

		$form = $admin->addForm('user', 'User', function (User $user = null) {
			return $this->getUserType()->getFormService()->getFormFactory($user ? $user->getId() : null);
		}, Form::TYPE_LARGE);
		$form->onSuccess[] = function () {
			$this->flashMessage('User has been saved.', 'success');
			$this->redrawControl('flashes');
		};
		$form->onError[] = function () {
			$this->flashMessage('Failed.', 'warning');
			$this->redrawControl('flashes');
		};

		$providerForm = $admin->addForm('loginProviders', 'Login providers', $this->providersForm, null, Form::TYPE_LARGE);

		$toolbar = $admin->getNavbar();
		$section = $toolbar->addSection('new', 'Create', 'user');

		$editAction = $table->addActionEvent('edit', 'Edit');
		$editAction->getElementPrototype()->class[] = 'ajax';

		$loginProviders = $table->addActionEvent('loginProviders', 'Login providers');
		$loginProviders->getElementPrototype()->class[] = 'ajax';

		$deleteAction = $table->addActionEvent('delete', 'Delete');
		$deleteAction->getElementPrototype()->class[] = 'ajax';

		$admin->connectFormWithAction($form, $editAction, $admin::MODE_PLACE);
		$admin->connectFormWithAction($providerForm, $loginProviders);
		$admin->connectActionAsDelete($deleteAction);
		foreach ($this->securityManager->getUserTypes() as $type => $value) {
			$admin->connectFormWithNavbar(
				$form,
				$section->addSection(str_replace('\\', '_', $type), $value->getName()),
				$admin::MODE_PLACE
			);
		}

		$admin->onDelete[] = function () {
			$this->flashMessage('User has been deleted.', 'success');
			$this->redrawControl('flashes');
		};

		return $admin;
	}

	/**
	 * @return \Venne\Security\User\UserType
	 */
	private function getUserType()
	{
		return $this->securityManager->getUserTypeByClass($this->type);
	}

	public function loadState(array $params)
	{
		parent::loadState($params);

		$this->page = isset($params['page']) ? $params['page'] : null;
		$this->type = isset($params['type']) ? $params['type'] : key($this->securityManager->getUserTypes());
	}

	public function saveState(array & $params, $reflection = null)
	{
		parent::saveState($params, $reflection);

		$params['page'] = $this->page;
		$params['type'] = $this->type;
	}

}
