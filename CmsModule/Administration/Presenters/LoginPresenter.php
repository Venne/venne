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

use CmsModule\Components\LoginControlFactory;
use CmsModule\Components\RegistrationControl;
use CmsModule\Components\RegistrationControlFactory;
use CmsModule\Security\Repositories\RoleRepository;
use CmsModule\Security\SecurityManager;
use Nette\Application\UI\Multiplier;
use Venne\Forms\Form;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class LoginPresenter extends BasePresenter
{

	/** @persistent */
	public $backlink;

	/** @var Callback */
	protected $form;

	/** @var string */
	protected $autologin;

	/** @var string */
	protected $autoregistration;

	/** @var array */
	protected $reset;

	/** @var array */
	protected $registrations = array();

	/** @var SecurityManager */
	private $securityManager;

	/** @var RoleRepository */
	private $roleRepository;

	/** @var RegistrationControlFactory */
	protected $registrationControlFactory;


	/**
	 * @param \CmsModule\Components\RegistrationControlFactory $registrationControlFactory
	 */
	public function injectRegistrationControlFactory(RegistrationControlFactory $registrationControlFactory)
	{
		$this->registrationControlFactory = $registrationControlFactory;
	}


	/**
	 * @param LoginControlFactory $form
	 * @param SecurityManager $securityManager
	 * @param RoleRepository $roleRepository
	 */
	public function __construct(LoginControlFactory $form, SecurityManager $securityManager, RoleRepository $roleRepository)
	{
		parent::__construct();

		$this->form = $form;
		$this->securityManager = $securityManager;
		$this->roleRepository = $roleRepository;
	}


	/**
	 * @param string $autologin
	 */
	public function setAutologin($autologin)
	{
		$this->autologin = $autologin;
	}


	/**
	 * @param array $reset
	 */
	public function setReset($reset)
	{
		$this->reset = $reset;
	}


	/**
	 * @param string $autoregistration
	 */
	public function setAutoregistration($autoregistration)
	{
		$this->autoregistration = $autoregistration;
	}


	/**
	 * @param array $registrations
	 */
	public function setRegistrations($registrations)
	{
		$this->registrations = $registrations;

		foreach ($this->registrations as $key => $reg) {
			if (!isset($reg['enabled']) || !$reg['enabled']) {
				unset($this->registrations[$key]);
			}
		}
	}


	/**
	 * @return array
	 */
	public function getRegistrations()
	{
		return $this->registrations;
	}


	protected function startup()
	{
		parent::startup();

		if (!$this->context->createCheckConnection()) {
			$this->flashMessage($this->translator->translate('Only administrator can be logged'), 'warning');
		}

		if ($this->user->isLoggedIn()) {
			$this->redirect(':Cms:Admin:' . $this->administrationManager->defaultPresenter . ':');
		}

		if ($this->autologin && !$this->getParameter('do') && !$this->template->flashes) {
			if (!$this['signInForm']->template->flashes) {
				$this->redirect('this', array('do' => 'signInForm-login', 'signInForm-name' => $this->autologin));
			}
		}
	}


	/**
	 * Sign in form component factory.
	 *
	 * @return Form
	 */
	protected function createComponentSignInForm()
	{
		$form = $this->form->invoke();
		$form->onSuccess[] = $this->formSuccess;
		$form->onError[] = $this->formError;

		if ($this->reset['enabled']) {
			$form->setResetEmail(
				$this->reset['emailSubject'],
				$this->reset['emailText'],
				$this->reset['emailSender'],
				$this->reset['emailFrom']
			);
		}

		return $form;
	}


	public function formSuccess()
	{
		if ($this->backlink) {
			$this->restoreRequest($this->backlink);
		}

		$this->redirect(':Cms:Admin:' . $this->administrationManager->defaultPresenter . ':');
	}


	public function formError($control, $message)
	{
		if ($this->autoregistration) {
			$registration = str_replace(' ', '_', $this->autoregistration);
			$this->redirect('this', array('do' => 'registration-' . $registration . '-load', 'registration-' . $registration . '-name' => $this->autologin));
		}

		$this->flashMessage($this->translator->translate($message), 'warning');
		$this->redirect('this');
	}


	protected function createComponentRegistration()
	{
		return new Multiplier($this->createRegistration);
	}


	public function createRegistration($name)
	{
		$name = str_replace('_', ' ', $name);

		/** @var RegistrationControl $control */
		$control = $this->registrationControlFactory->invoke(
			$this->registrations[$name]['userType'],
			$this->registrations[$name]['mode'],
			$this->registrations[$name]['loginProviderMode'],
			$this->registrations[$name]['roles'],
			$this->registrations[$name]['email']['sender'],
			$this->registrations[$name]['email']['from'],
			$this->registrations[$name]['email']['subject'],
			$this->registrations[$name]['email']['text']
		);

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
