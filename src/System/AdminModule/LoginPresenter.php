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
use Nette\Application\UI\Presenter;
use Venne\System\AdminPresenterTrait;
use Venne\Security\Registration\IRegistrationControlFactory;
use Venne\Security\Registration\RegistrationControl;
use Venne\Security\Login\ILoginControlFactory;
use Venne\Security\SecurityManager;
use Nette\Application\UI\Multiplier;
use Venne\Forms\Form;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 */
class LoginPresenter extends Presenter
{

	use AdminPresenterTrait;

	/** @persistent */
	public $backlink;

	/** @var Callback */
	private $form;

	/** @var string */
	private $autologin;

	/** @var string */
	private $autoregistration;

	/** @var array */
	private $registrations = array();

	/** @var SecurityManager */
	private $securityManager;

	/** @var EntityDao */
	private $roleDao;

	/** @var RegistrationControlFactory */
	private $registrationControlFactory;


	/**
	 * @param EntityDao $roleDao
	 * @param ILoginControlFactory $form
	 * @param IRegistrationControlFactory $registrationControlFactory
	 * @param SecurityManager $securityManager
	 */
	public function __construct(
		EntityDao $roleDao,
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
	 * Sign in form component factory.
	 *
	 * @return Form
	 */
	protected function createComponentSignInForm()
	{
		$form = $this->form->create();
		$form->onSuccess[] = $this->formSuccess;
		$form->onError[] = $this->formError;
		return $form;
	}


	public function formSuccess()
	{
		if ($this->backlink) {
			$this->restoreRequest($this->backlink);
		}

		$this->redirect(':' . $this->administrationManager->defaultPresenter . ':');
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
		$control = $this->registrationControlFactory->create(
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
