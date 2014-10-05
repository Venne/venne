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
use Grido\DataSources\ArraySource;
use Grido\DataSources\Doctrine;
use Nette\Forms\Form;
use Nette\Http\Session;
use Nette\Utils\Html;
use Venne\Bridges\Kdyby\DoctrineForms\FormFactoryFactory;
use Venne\Security\DefaultType\RegistrationFormFactory;
use Venne\Security\Login;
use Venne\Security\SecurityManager;
use Venne\Security\User;
use Venne\System\Components\AdminGrid\IAdminGridFactory;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class AccountPresenter extends \Nette\Application\UI\Presenter
{

	use \Venne\System\AdminPresenterTrait;

	/**
	 * @var string
	 *
	 * @persistent
	 */
	public $provider;

	/** @var \Kdyby\Doctrine\EntityRepository */
	private $userRepository;

	/** @var \Kdyby\Doctrine\EntityRepository */
	private $loginRepository;

	/** @var \Venne\Security\DefaultType\RegistrationFormFactory */
	private $userFormFactory;

	/** @var \Venne\Security\AdminModule\ProviderFormFactory */
	private $providerFormFactory;

	/** @var \Venne\Security\SecurityManager */
	private $securityManager;

	/** @var \Venne\System\Components\AdminGrid\IAdminGridFactory */
	private $adminGridFactory;

	/** @var \Nette\Http\Session */
	private $session;

	/** @var \Venne\Bridges\Kdyby\DoctrineForms\FormFactoryFactory */
	private $formFactoryFactory;

	public function __construct(
		EntityManager $entityManager,
		RegistrationFormFactory $userFormFactory,
		ProviderFormFactory $providerFormFactory,
		SecurityManager $securityManager,
		IAdminGridFactory $adminGridFactory,
		Session $session,
		FormFactoryFactory $formFactoryFactory
	)
	{
		$this->userRepository = $entityManager->getRepository(User::class);
		$this->loginRepository = $entityManager->getRepository(Login::class);
		$this->userFormFactory = $userFormFactory;
		$this->providerFormFactory = $providerFormFactory;
		$this->securityManager = $securityManager;
		$this->adminGridFactory = $adminGridFactory;
		$this->session = $session;
		$this->formFactoryFactory = $formFactoryFactory;
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

	/**
	 * @param string $service
	 * @param string[]|null $parameters
	 */
	public function handleConnect($service, $parameters = null)
	{
		$login = $this->securityManager->getLoginProviderByName($service);

		if ($parameters) {
			$login->setAuthenticationParameters(json_decode($parameters, true));
		}

		$login->connectWithUser($this->extendedUser->getUser());

		$this->redirect('this', array('provider' => null, 'loginTable-id' => null, 'loginTable-formName' => null));
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

		$this->getEntityManager()->flush($user);

		$this->redirect('this', array('provider' => null));
	}

	public function renderDefault()
	{
		$this->template->securityManager = $this->securityManager;
	}

	/**
	 * @return \Venne\System\Components\AdminGrid\AdminGrid
	 */
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

		$admin = $this->adminGridFactory->create($this->loginRepository);

		// columns
		$table = $admin->getTable();
		$table->setModel(new ArraySource($data));
		$table->setTranslator($this->translator);
		$table->addColumnText('name', 'Name')
			->getCellPrototype()->width = '100%';

		/** @var \Venne\Security\User $user */
		$user = $this->user->identity;
		$securityManager = $this->securityManager;
		$providerFormFactory = $this->providerFormFactory;

		// actions
		$table->addActionEvent('connect', 'Connect')
			->setCustomRender(function ($entity, $element) use ($securityManager, $user) {
				if ($user->hasLoginProvider($entity['name'])) {
					$element->class[] = 'disabled';
				} else {
					$element->class[] = 'btn-primary';
				}

				return $element;
			});
		$table->getAction('connect')->onClick[] = function ($button, $name) use ($_this, $securityManager, $providerFormFactory, $user) {
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
		$this->providerFormFactory->onSave[] = function (Form $form) use ($_this) {
			$_this->redirect('connect!', array($form['provider']->value, json_encode($form['parameters']->values)));
		};
		$this->providerFormFactory->onSuccess[] = function ($parameters) use ($_this) {
			$_this->redirect('this');
		};
		$form = $admin->addForm('provider', 'Provider', $this->providerFormFactory);
		$admin->connectFormWithAction($form, $table->getAction('connect'));

		$table->addActionEvent('disconnect', 'Disconnect')
			->setCustomRender(function ($entity, $element) use ($securityManager, $user) {
				if (!$user->hasLoginProvider($entity['name'])) {
					$element->class[] = 'disabled';
				};

				return $element;
			})
			->setConfirm(function ($entity) {
				return array('Really disconnect from \'%s\'?', $entity['name']);
			});
		$table->getAction('disconnect')->onClick[] = function ($button, $name) use ($_this) {
			$_this->handleDisconnect(str_replace('_', ' ', $name));
		};
		$table->getAction('disconnect')->getElementPrototype()->class[] = 'ajax';

		return $admin;
	}

	/**
	 * @return \Venne\System\Components\AdminGrid\AdminGrid
	 */
	protected function createComponentTable()
	{
		$session = $this->session;
		$admin = $this->adminGridFactory->create($this->loginRepository);

		// columns
		$table = $admin->getTable();
		if ($this->user->identity instanceof User) {
			$table->setModel(new Doctrine($this->loginRepository->createQueryBuilder('a')->andWhere('a.user = :user')->setParameter('user', $this->user->identity)));
		} else {
			$table->setModel(new Doctrine($this->loginRepository->createQueryBuilder('a')->andWhere('a.user IS NULL')));
		}
		$table->setTranslator($this->translator);
		$table->addColumnDate('current', 'Current')
			->setCustomRender(function (Login $entity) use ($session) {
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
		$table->addActionEvent('delete', 'Delete')
			->getElementPrototype()->class[] = 'ajax';
		$admin->connectActionAsDelete($table->getAction('delete'));

		return $admin;
	}

	/**
	 * @return \Nette\Forms\Form
	 */
	protected function createComponentAccountForm()
	{
		$user = $this->userRepository->find($this->getUser()->getIdentity()->getId());

		$formService = $this->securityManager
			->getUserTypeByClass($user->getClass())
			->getFrontFormService();

		$form = $formService
			->createFormFactory($user->getExtendedUser())
			->create();

		$form->onSuccess[] = $this->accountFormSuccess;

		return $form;
	}

	public function accountFormSuccess()
	{
		$this->flashMessage($this->translator->translate('Account settings has been updated.'), 'success');
		$this->redirect('this');
	}

}
