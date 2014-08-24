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

use Kdyby\Doctrine\EntityDao;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Multiplier;
use Venne\Forms\Form;
use Venne\Security\Login\ILoginControlFactory;
use Venne\Security\Login\LoginControl;
use Venne\Security\Registration\IRegistrationControlFactory;
use Venne\Security\Registration\RegistrationControl;
use Venne\Security\SecurityManager;

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

	/**
	 * @var string|null
	 *
	 * @persistent
	 */
	public $registrationKey;

	/**
	 * @var string|null
	 *
	 * @persistent
	 */
	public $hash;

	/** @var Callback */
	private $form;

	/** @var string */
	private $autologin;

	/** @var string */
	private $autoregistration;

	/** @var \Venne\Security\SecurityManager */
	private $securityManager;

	/** @var \Kdyby\Doctrine\EntityDao */
	private $roleDao;

	/** @var \Kdyby\Doctrine\EntityDao */
	private $registrationDao;

	/** @var \Kdyby\Doctrine\EntityDao */
	private $invitationDao;

	/** @var \Venne\Security\Registration\IRegistrationControlFactory */
	private $registrationControlFactory;

	public function __construct(
		EntityDao $roleDao,
		EntityDao $registrationDao,
		EntityDao $invitationDao,
		ILoginControlFactory $form,
		IRegistrationControlFactory $registrationControlFactory,
		SecurityManager $securityManager
	)
	{
		parent::__construct();

		$this->form = $form;
		$this->registrationControlFactory = $registrationControlFactory;
		$this->securityManager = $securityManager;
		$this->roleDao = $roleDao;
		$this->registrationDao = $registrationDao;
		$this->invitationDao = $invitationDao;
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
	 * @return \Kdyby\Doctrine\EntityDao
	 */
	public function getRegistrationDao()
	{
		return $this->registrationDao;
	}

	protected function startup()
	{
		parent::startup();

		$this->redrawControl('sideComponent-container');

		if ($this->user->isLoggedIn()) {
			$this->redirect(':' . $this->administrationManager->defaultPresenter . ':');
		}

		if ($this->autologin && !$this->getParameter('do') && !$this->template->flashes && !$this['signInForm']['form']->isSubmitted()) {
			if (!$this['signInForm']->template->flashes) {
				$this->redirect('this', array('do' => 'signInForm-login', 'signInForm-name' => $this->autologin));
			}
		}
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
	 * @param string $name
	 * @return \Venne\Security\Registration\RegistrationControl
	 */
	public function createRegistration($name)
	{
		if (!$registration = $this->registrationDao->findOneBy(array('id' => $this->registrationKey, 'enabled' => true))) {
			throw new BadRequestException;
		}

		if ($registration->invitation && !$this->hash) {
			throw new BadRequestException;
		}

		if ($this->hash && !($invitation = $this->invitationDao->findOneBy(array('hash' => $this->hash, 'registration' => $this->registrationKey)))) {
			throw new BadRequestException;
		}

		/** @var RegistrationControl $control */
		$control = $this->registrationControlFactory->create(
			$registration->getInvitation(),
			$registration->userType,
			$registration->mode,
			$registration->loginProviderMode,
			$registration->roles
		);

		if ($this->hash && $invitation) {
			$control->setDefaultEmail($invitation->email);
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
		if ($this->hash) {
			$invitation = $this->invitationDao->findOneBy(array('hash' => $this->hash, 'registration' => $this->registrationKey));
			$this->invitationDao->delete($invitation);
		}

		$this->flashMessage($this->translator->translate('Your registration is complete'), 'success');
		$this->redirect('this');
	}

	public function registrationEnable()
	{
		$this->flashMessage($this->translator->translate('Your registration is complete'), 'success');
		$this->redirect('this');
	}

	public function registrationError($control, $message)
	{
		$this->flashMessage($this->translator->translate($message), 'warning');
		$this->redirect('this');
	}

}
