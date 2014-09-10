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

use Venne\Security\AdminModule\AccountFormFactory;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 *
 * @secured
 */
class ApplicationPresenter extends \Nette\Application\UI\Presenter
{

	use \Venne\System\AdminPresenterTrait;

	/** @var \Venne\System\AdminModule\AdministrationFormFactory */
	private $systemForm;

	/** @var \Venne\System\AdminModule\ApplicationFormFactory */
	private $applicationForm;

	/** @var \Venne\Security\AdminModule\AccountFormFactory */
	private $accountForm;

	/** @var \Venne\System\AdminModule\MailerFormFactory */
	private $mailerForm;

	/** @var \Venne\System\AdminModule\AuthenticationFormFactory */
	private $authenticationForm;

	/** @var RegistrationTableFactory */
	private $registrationTableFactory;

	public function __construct(
		ApplicationFormFactory $applicationForm,
		AccountFormFactory $accountForm,
		AdministrationFormFactory $systemForm,
		MailerFormFactory $mailerForm,
		AuthenticationFormFactory $authenticationForm,
		RegistrationTableFactory $registrationTableFactory
	)
	{
		$this->authenticationForm = $authenticationForm;
		$this->applicationForm = $applicationForm;
		$this->accountForm = $accountForm;
		$this->systemForm = $systemForm;
		$this->mailerForm = $mailerForm;
		$this->registrationTableFactory = $registrationTableFactory;
	}

	/**
	 * @secured(privilege="system")
	 */
	public function actionDefault()
	{
	}

	/**
	 * @secured
	 */
	public function actionDatabase()
	{
	}

	/**
	 * @secured
	 */
	public function actionAccount()
	{
	}

	/**
	 * @secured
	 */
	public function actionAdmin()
	{
	}

	/**
	 * @secured
	 */
	public function actionMailer()
	{
	}

	/**
	 * @secured
	 */
	public function actionRegistration()
	{
	}

	/**
	 * @secured
	 */
	public function actionAuthentication()
	{
	}

	/**
	 * @return \Nette\Application\UI\Form
	 */
	protected function createComponentSystemForm()
	{
		$form = $this->systemForm->create();

		return $form;
	}

	/**
	 * @return \Nette\Application\UI\Form
	 */
	protected function createComponentApplicationForm()
	{
		$form = $this->applicationForm->create();
		$form->onSuccess[] = function () {
			$this->flashMessage($this->translator->translate('Application settings has been updated.'), 'success');
			$this->redirect('this');
		};

		return $form;
	}

	/**
	 * @return \Nette\Application\UI\Form
	 */
	protected function createComponentAccountForm()
	{
		$form = $this->accountForm->create();
		$form->setDefaults(array(
			'routePrefix' => $this->administrationManager->routePrefix,
			'defaultPresenter' => $this->administrationManager->defaultPresenter,
		));
		$form->onSuccess[] = function () {
			$this->flashMessage($this->translator->translate('Account settings has been updated.'), 'success');
			$this->redirect('this');
		};

		return $form;
	}

	/**
	 * @return \Nette\Application\UI\Form
	 */
	protected function createComponentMailerForm()
	{
		$form = $this->mailerForm->create();
		$form->onSuccess[] = function () {
			$this->flashMessage($this->translator->translate('Mailer settings has been updated.'), 'success');
			$this->redirect('this');
		};

		return $form;
	}

	/**
	 * @return \Venne\System\Components\AdminGrid\AdminGrid
	 */
	protected function createComponentRegistrationTable()
	{
		return $this->registrationTableFactory->create();
	}

	/**
	 * @return \Nette\Application\UI\Form
	 */
	protected function createComponentAuthenticationForm()
	{
		$form = $this->authenticationForm->create();
		$form->onSuccess[] = function () {
			$this->flashMessage($this->translator->translate('Authentication settings has been updated.'), 'success');
			$this->redirect('this');
		};

		return $form;
	}

}
