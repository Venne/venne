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

use Doctrine\ORM\EntityManager;
use Nette\Application\UI\Multiplier;
use Venne\Forms\Form;
use Venne\Security\Login\ILoginControlFactory;
use Venne\Security\Login\LoginControl;
use Venne\Security\Registration\IRegistrationControlFactory;
use Venne\Security\Registration\RegistrationControl;
use Venne\Security\Role;
use Venne\Security\SecurityManager;
use Venne\System\Invitation;
use Venne\System\Registration;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class LoginPresenter extends \Nette\Application\UI\Presenter
{

	use \Venne\System\AdminPresenterTrait;

	/**
	 * @var string|null
	 *
	 * @persistent
	 */
	public $backlink;

	/** @var Callback */
	private $form;

	/** @var string */
	private $autologin;

	/** @var string */
	private $autoregistration;

	/** @var \Venne\Security\SecurityManager */
	private $securityManager;

	/** @var \Kdyby\Doctrine\EntityRepository */
	private $roleRepository;

	/** @var \Kdyby\Doctrine\EntityRepository */
	private $registrationRepository;

	/** @var \Kdyby\Doctrine\EntityRepository */
	private $invitationRepository;

	/** @var \Venne\Security\Registration\IRegistrationControlFactory */
	private $registrationControlFactory;

	/** @var \Venne\System\Registration|null */
	private $registration;

	/** @var \Venne\System\Invitation|null */
	private $invitation;

	public function __construct(
		EntityManager $entityManager,
		ILoginControlFactory $form,
		IRegistrationControlFactory $registrationControlFactory,
		SecurityManager $securityManager
	)
	{
		parent::__construct();

		$this->roleRepository = $entityManager->getRepository(Role::class);
		$this->registrationRepository = $entityManager->getRepository(Registration::class);
		$this->invitationRepository = $entityManager->getRepository(Invitation::class);
		$this->form = $form;
		$this->registrationControlFactory = $registrationControlFactory;
		$this->securityManager = $securityManager;
	}

	/**
	 * @param string $autologin
	 */
	public function setAutologin($autologin)
	{
		$this->autologin = $autologin;
	}

	/**
	 * @param string $autoregistration
	 */
	public function setAutoregistration($autoregistration)
	{
		$this->autoregistration = $autoregistration;
	}

	/**
	 * @return \Kdyby\Doctrine\EntityRepository
	 */
	public function getRegistrationRepository()
	{
		return $this->registrationRepository;
	}

	protected function startup()
	{
		parent::startup();

		$this->redrawControl('sideComponent-container');

		if ($this->user->isLoggedIn()) {
			$this->redirect(':Admin:' . $this->administrationManager->defaultPresenter . ':');
		}

		if ($this->autologin && !$this->getParameter('do') && !$this->template->flashes && !$this['signInForm']['form']->isSubmitted()) {
			if (!$this['signInForm']->template->flashes) {
				$this->redirect('this', array('do' => 'signInForm-login', 'signInForm-name' => $this->autologin));
			}
		}
	}

	public function handleChange()
	{
		$this->redirect('this');
		$this->redrawControl('loginContent');
	}

	public function renderDefault()
	{
		$this->template->invitation = $this->invitation;
		$this->template->registration = $this->registration;
		$this->template->registrations = $this->registrationRepository->findBy(array(
			'enabled' => true,
			'invitation' => false,
		));
	}

	/**
	 * @return \Venne\Security\Login\LoginControl
	 */
	protected function createComponentSignInForm()
	{
		$form = $this->form->create();
		$form->onSuccess[] = $this->formSuccess;
		$form->onError[] = $this->formError;

		return $form;
	}

	/**
	 * @internal
	 */
	public function formSuccess()
	{
		if ($this->backlink) {
			$this->restoreRequest($this->backlink);
		}

		$this->redirect(':' . $this->administrationManager->defaultPresenter . ':');
	}

	/**
	 * @param \Venne\Security\Login\LoginControl $control
	 * @param string $message
	 * @internal
	 */
	public function formError(LoginControl $control, $message)
	{
		if ($this->autoregistration) {
			$registration = str_replace(' ', '_', $this->autoregistration);
			$this->redirect('this', array('do' => 'registration-' . $registration . '-load', 'registration-' . $registration . '-name' => $this->autologin));
		}

		$this->flashMessage($this->translator->translate($message), 'warning');
		$this->redirect('this');
	}

	/**
	 * @return \Nette\Application\UI\Multiplier
	 */
	protected function createComponentRegistration()
	{
		return new Multiplier($this->createRegistration);
	}

	/**
	 * @return \Venne\Security\Registration\RegistrationControl
	 */
	public function createRegistration()
	{
		/** @var RegistrationControl $control */
		$control = $this->registrationControlFactory->create(
			$this->registration->getInvitation(),
			$this->registration->userType,
			$this->registration->mode,
			$this->registration->loginProviderMode,
			$this->registration->roles
		);

		if ($this->invitation) {
			$control->setDefaultEmail($this->invitation->getEmail());
		}

		$control->onLoad[] = $this->registrationLoad;
		$control->onSuccess[] = $this->registrationSuccess;
		$control->onEnable[] = $this->registrationEnable;
		$control->onError[] = $this->registrationError;

		return $control;
	}

	public function registrationLoad(RegistrationControl $control)
	{
		$this->template->regLoad = $control->name;
	}

	public function registrationSuccess()
	{
		$this->invitationRepository->delete($this->invitation);

		$this->flashMessage($this->translator->translate('Your registration is complete.'), 'success');
		$this->redirect('this', array(
			'hash' => null,
			'registration' => null,
		));
	}

	public function registrationEnable()
	{
		$this->flashMessage($this->translator->translate('Your registration is complete.'), 'success');
		$this->redirect('this');
	}

	public function registrationError(RegistrationControl $control, $message)
	{
		$this->flashMessage($this->translator->translate($message), 'warning');
		$this->redirect('this');
	}

	public function loadState(array $params)
	{
		if (isset($params['registration'])) {
			$this->registration = $this->registrationRepository->findOneBy(array(
				'id' => $params['registration'],
				'enabled' => true,
			));

			if ($this->registration === null) {
				$this->error();
			}

			if (isset($params['hash'])) {
				$this->invitation = $this->invitationRepository->findOneBy(array(
					'hash' => $params['hash'],
					'registration' => $params['registration'],
				));

				if ($this->invitation === null) {
					$this->error();
				}
			}
		}

		parent::loadState($params);
	}

	public function saveState(array & $params, $reflection = null)
	{
		if ($this->registration !== null) {
			$params['registration'] = $this->registration->getId();
		}

		if ($this->invitation !== null) {
			$params['hash'] = $this->invitation->getHash();
		}

		parent::saveState($params, $reflection);
	}

}
