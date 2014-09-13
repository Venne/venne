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

use Doctrine\ORM\Query;
use Grido\DataSources\Doctrine;
use Kdyby\Doctrine\EntityDao;
use Venne\Security\DefaultType\AdminFormFactory;
use Venne\Security\SecurityManager;
use Venne\System\Components\AdminGrid\Form;
use Venne\System\Components\AdminGrid\IAdminGridFactory;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 *
 * @secured
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

	/** @var \Kdyby\Doctrine\EntityDao */
	private $userDao;

	/** @var \Venne\Security\DefaultType\AdminFormFactory */
	private $form;

	/** @var \Venne\Security\AdminModule\ProvidersFormFactory */
	private $providersForm;

	/** @var \Venne\Security\SecurityManager */
	private $securityManager;

	/** @var \Venne\System\Components\AdminGrid\IAdminGridFactory */
	private $adminGridFactory;

	public function __construct(
		EntityDao $userDao,
		AdminFormFactory $form,
		ProvidersFormFactory $providersForm,
		SecurityManager $securityManager,
		IAdminGridFactory $adminGridFactory
	)
	{
		$this->userDao = $userDao;
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
		$dao = $this->entityManager->getDao($this->type);
		$admin = $this->adminGridFactory->create($dao);
		$table = $admin->getTable();
		$table->setTranslator($this->translator);
		$table->setModel(new Doctrine($dao->createQueryBuilder('a')
				->addSelect('u.email')
				->innerJoin('a.user', 'u'),
			null,
			null,
			Query::HYDRATE_ARRAY
		));
		$table->setPrimaryKey('user_id');

		// columns
		$table->addColumnText('email', 'E-mail')
			->setSortable()
			->getCellPrototype()->width = '100%';
		$table->getColumn('email')
			->setFilterText()->setSuggestion();

		// actions
		$table->addActionEvent('edit', 'Edit')
			->getElementPrototype()->class[] = 'ajax';

		$table->addActionEvent('loginProviders', 'Login providers')
			->getElementPrototype()->class[] = 'ajax';

		$type = $this->type;
		$form = $admin->createForm($this->getUserType()->getFormFactory(), 'User', function () use ($type) {
			return new $type;
		}, Form::TYPE_LARGE);
		$providerForm = $admin->createForm($this->providersForm, 'Login providers', null, Form::TYPE_LARGE);

		$admin->connectFormWithAction($form, $table->getAction('edit'), $admin::MODE_PLACE);
		$admin->connectFormWithAction($providerForm, $table->getAction('loginProviders'));

		// Toolbar
		$toolbar = $admin->getNavbar();
		$toolbar->addSection('new', 'Create', 'file');
		$admin->connectFormWithNavbar($form, $toolbar->getSection('new'), $admin::MODE_PLACE);

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
