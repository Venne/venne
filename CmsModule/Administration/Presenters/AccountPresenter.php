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

use CmsModule\Administration\Components\AdminGrid\AdminGrid;
use CmsModule\Forms\ProviderFormFactory;
use CmsModule\Forms\SystemAccountFormFactory;
use CmsModule\Pages\Users\AdminUserFormFactory;
use CmsModule\Pages\Users\UserEntity;
use CmsModule\Pages\Users\UsersEntity;
use CmsModule\Security\SecurityManager;
use CmsModule\Security\Entities\LoginEntity;
use CmsModule\Security\Repositories\LoginRepository;
use CmsModule\Security\Repositories\UserRepository;
use Grido\DataSources\ArraySource;
use Grido\DataSources\Doctrine;
use Nette\Http\Session;
use Nette\Utils\Html;
use Venne\Forms\Form;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 *
 * @secured
 */
class AccountPresenter extends BasePresenter
{

	/** @persistent */
	public $provider;

	/** @var UserRepository */
	protected $userRepository;

	/** @var LoginRepository */
	protected $loginRepository;

	/** @var SystemAccountFormFactory */
	protected $accountFormFactory;

	/** @var AdminUserFormFactory */
	protected $userFormFactory;

	/** @var ProviderFormFactory */
	protected $providerFormFactory;

	/** @var SecurityManager */
	protected $securityManager;

	/** @var Session */
	protected $session;


	/**
	 * @param UserRepository $userRepository
	 */
	public function injectUserRepository(UserRepository $userRepository)
	{
		$this->userRepository = $userRepository;
	}


	/**
	 * @param LoginRepository $loginRepository
	 */
	public function injectLoginRepository(LoginRepository $loginRepository)
	{
		$this->loginRepository = $loginRepository;
	}


	/**
	 * @param SystemAccountFormFactory $accountFormFactory
	 */
	public function injectAccountFormFactory(SystemAccountFormFactory $accountFormFactory)
	{
		$this->accountFormFactory = $accountFormFactory;
	}


	/**
	 * @param AdminUserFormFactory $userFormFactory
	 */
	public function injectUserFormFactory(AdminUserFormFactory $userFormFactory)
	{
		$this->userFormFactory = $userFormFactory;
	}


	/**
	 * @param \CmsModule\Forms\ProviderFormFactory $providerFormFactory
	 */
	public function injectProviderFormFactory(ProviderFormFactory $providerFormFactory)
	{
		$this->providerFormFactory = $providerFormFactory;
	}





	/**
	 * @param SecurityManager $securityManager
	 */
	public function injectSecurityManager(SecurityManager $securityManager)
	{
		$this->securityManager = $securityManager;
	}


	/**
	 * @param Session $session
	 */
	public function injectSession(Session $session)
	{
		$this->session = $session;
	}


	/**
	 * @secured(privilege="show")
	 */
	public function actionDefault()
	{
	}


	/**
	 * @secured
	 */
	public function actionEdit()
	{
	}


	public function handleConnect($service, $parameters = NULL)
	{
		$login = $this->securityManager->getLoginProviderByName($service);

		if ($parameters) {
			$login->setAuthenticationParameters(json_decode($parameters, TRUE));
		}

		$login->connectWithUser($this->extendedUser->getUser());

		$this->redirect('this', array('provider' => NULL, 'loginTable-id' => NULL, 'loginTable-formName' => NULL));
	}


	public function handleDisconnect($service)
	{
		$user = $this->extendedUser->getUser();

		foreach ($user->getLoginProviders() as $key => $item) {
			if ($item->getType() === $service) {
				$user->getLoginProviders()->remove($key);
				break;
			}
		}

		$this->userRepository->save($user);

		$this->redirect('this', array('provider' => NULL));
	}


	public function renderDefault()
	{
		$this->template->securityManager = $this->securityManager;
	}

	protected function createComponentLoginTable()
	{
		$_this = $this;
		$data = array();

		foreach ($this->securityManager->getLoginProviders() as $name) {
			$data[] = array(
				'id' => str_replace(' ', '_', $name),
				'name' => $name,
			);
		}

		$admin = new AdminGrid($this->loginRepository);

		// columns
		$table = $admin->getTable();
		$table->setModel(new ArraySource($data));
		$table->setTranslator($this->translator);
		$table->addColumnText('name', 'Name')
			->getCellPrototype()->width = '100%';


		/** @var UserEntity $user */
		$user = $this->user->identity;
		$securityManager = $this->securityManager;
		$providerFormFactory = $this->providerFormFactory;

		// actions
		$table->addAction('connect', 'Connect')
			->setCustomRender(function ($entity, $element) use ($securityManager, $user) {
				if ($user->hasLoginProvider($entity['name'])) {
					$element->class[] = 'disabled';
				} else {
					$element->class[] = 'btn-primary';
				}
				return $element;
			});
		$table->getAction('connect')->onClick[] = function($button, $name) use ($_this, $securityManager, $providerFormFactory, $user) {
			if (!$securityManager->getLoginProviderByName(str_replace('_', ' ', $name))->getFormContainer()) {
				$_this->redirect('connect!', str_replace('_', ' ', $name));
			} else {
				$_this->provider = str_replace('_', ' ', $name);
				$providerFormFactory->setProvider($_this->provider);
			}
		};
		$table->getAction('connect');

			$this->providerFormFactory->setUser($user);
		if ($this->provider) {
			$this->providerFormFactory->setProvider($this->provider);
		}
		$this->providerFormFactory->onSave[] = function(Form $form) use ($_this) {
			$_this->redirect('connect!', array($form['provider']->value, json_encode($form['parameters']->values)));
		};
		$this->providerFormFactory->onSuccess[] = function($parameters) use ($_this) {
			$_this->redirect('this');
		};
		$form = $admin->createForm($this->providerFormFactory, 'Provider');
		$admin->connectFormWithAction($form, $table->getAction('connect'));


		$table->addAction('disconnect', 'Disconnect')
			->setCustomRender(function ($entity, $element) use ($securityManager, $user) {
				if (!$user->hasLoginProvider($entity['name'])) {
					$element->class[] = 'disabled';
				};
				return $element;
			})
			->setConfirm(function ($entity) {
				return "Really disconnect from '{$entity['name']}'?";
			});
		$table->getAction('disconnect')->onClick[] = function($button, $name) use ($_this) {
			$_this->handleDisconnect(str_replace('_', ' ', $name));
		};
		$table->getAction('disconnect')->getElementPrototype()->class[] = 'ajax';

		return $admin;
	}


	protected function createComponentTable()
	{
		$session = $this->session;
		$admin = new AdminGrid($this->loginRepository);

		// columns
		$table = $admin->getTable();
		if ($this->user->identity instanceof UserEntity) {
			$table->setModel(new Doctrine($this->loginRepository->createQueryBuilder('a')->andWhere('a.user = :user')->setParameter('user', $this->user->identity)));
		} else {
			$table->setModel(new Doctrine($this->loginRepository->createQueryBuilder('a')->andWhere('a.user IS NULL')));
		}
		$table->setTranslator($this->translator);
		$table->addColumnDate('current', 'Current')
			->setCustomRender(function (LoginEntity $entity) use ($session) {
				$el = Html::el('span');
				$el->class[] = 'glyphicon ' . ($session->id == $entity->getSessionId() ? 'glyphicon-ok' : 'glyphicon-remove');
				return $el;
			})
			->getCellPrototype()->width = '10%';
		$table->addColumnDate('created', 'Date')
			->setSortable()
			->getCellPrototype()->width = '40%';
		$table->addColumnText('sessionId', 'Session ID')
			->getCellPrototype()->width = '50%';

		// actions
		$table->addAction('delete', 'Delete')
			->setConfirm(function ($entity) {
				return "Really delete session '{$entity->sessionId}'?";
			})
			->getElementPrototype()->class[] = 'ajax';
		$admin->connectActionAsDelete($table->getAction('delete'));

		return $admin;
	}


	protected function createComponentAccountForm()
	{
		if ($this->user->identity instanceof UserEntity) {
			$form = $this->securityManager
				->getUserTypeByClass($this->user->identity->class)
				->getFrontFormFactory()
				->invoke($this->extendedUser);
		} else {
			$form = $this->accountFormFactory->invoke();
		}

		$form->onSuccess[] = $this->accountFormSuccess;
		return $form;
	}


	public function accountFormSuccess()
	{
		$this->flashMessage($this->translator->translate('Account settings has been updated'), 'success');
		$this->redirect('this');
	}
}
