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
use Venne\Security\User\DefaultType\RegistrationFormFactory;
use Venne\Security\Login\Login;
use Venne\Security\SecurityManager;
use Venne\Security\User\User;
use Venne\System\Components\AdminGrid\IAdminGridFactory;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class AccountPresenter extends \Nette\Application\UI\Presenter
{

	use \Venne\System\AdminPresenterTrait;

	/** @var string */
	private $provider;

	/** @var \Kdyby\Doctrine\EntityRepository */
	private $userRepository;

	/** @var \Kdyby\Doctrine\EntityRepository */
	private $loginRepository;

	/** @var \Venne\Security\User\DefaultType\RegistrationFormFactory */
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
	) {
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

		/** @var \Venne\Security\User\User $user */
		$user = $this->getUser()->getIdentity();
		$securityManager = $this->securityManager;
		$providerFormFactory = $this->providerFormFactory;

		$connectAction = $table->addActionEvent('connect', 'Connect')
			->setCustomRender(function ($entity, $element) use ($securityManager, $user) {
				if ($user->hasLoginProvider($entity['name'])) {
					$element->class[] = 'disabled';
				} else {
					$element->class[] = 'btn-primary';
				}

				return $element;
			});
		$connectAction->onClick[] = function ($button, $name) use ($securityManager, $providerFormFactory, $user) {
			if (!$securityManager->getLoginProviderByName(str_replace('_', ' ', $name))->getFormContainer()) {
				$this->redirect('connect!', str_replace('_', ' ', $name));
			} else {
				$this->provider = str_replace('_', ' ', $name);
				$providerFormFactory->setProvider($this->provider);
			}
		};

		$this->providerFormFactory->setUser($user);
		if ($this->provider) {
			$this->providerFormFactory->setProvider($this->provider);
		}
		$this->providerFormFactory->onSave[] = function (Form $form) {
			$this->redirect('connect!', array($form['provider']->value, json_encode($form['parameters']->values)));
		};
		$this->providerFormFactory->onSuccess[] = function ($parameters) {
			$this->redirect('this');
		};
		$form = $admin->addForm('provider', 'Provider', $this->providerFormFactory);

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
		$table->getAction('disconnect')->onClick[] = function ($button, $name) {
			$this->handleDisconnect(str_replace('_', ' ', $name));
		};
		$table->getAction('disconnect')->getElementPrototype()->class[] = 'ajax';

		$admin->connectFormWithAction($form, $connectAction);

		return $admin;
	}

	/**
	 * @return \Venne\System\Components\AdminGrid\AdminGrid
	 */
	protected function createComponentTable()
	{
		$session = $this->getSession();
		$admin = $this->adminGridFactory->create($this->loginRepository);

		$table = $admin->getTable();
		$table->setPrimaryKey('session_id');
		if ($this->user->identity instanceof User) {
			$table->setModel(new Doctrine($this->loginRepository->createQueryBuilder('a')->andWhere('a.user = :user')->orderBy('a.created', 'DESC')->setParameter('user', $this->user->identity)));
		} else {
			$table->setModel(new Doctrine($this->loginRepository->createQueryBuilder('a')->andWhere('a.user IS NULL')->orderBy('a.created', 'DESC')));
		}
		$table->setTranslator($this->translator);
		$table->addColumnDate('current', 'Current')
			->setCustomRender(function (Login $entity) use ($session) {
				$el = Html::el('span');
				$el->class[] = 'glyphicon ' . ($session->getId() === $entity->getSessionId() ? 'glyphicon-ok' : 'glyphicon-remove');

				return $el;
			})
			->getCellPrototype()->width = '10%';
		$table->addColumnDate('created', 'Date')
			->getCellPrototype()->width = '40%';
		$table->addColumnText('sessionId', 'Session ID')
			->getCellPrototype()->width = '50%';

		$deleteAction = $table->addActionEvent('delete', 'Delete');
		$deleteAction->getElementPrototype()->class[] = 'ajax';

		$admin->connectActionAsDelete($deleteAction);

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
			->getFormFactory($user->getId())
			->create();

		$form->onSuccess[] = function () {
			$this->flashMessage($this->translator->translate('Account settings has been updated.'), 'success');
			$this->redirect('this');

			$this->redrawControl('content');
		};

		return $form;
	}

}
